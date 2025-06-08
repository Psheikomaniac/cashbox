<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

readonly class RateLimiterListener
{
    public function __construct(
        private RateLimiterFactory $loginLimiter,
        private RateLimiterFactory $apiLimiter,
        private RateLimiterFactory $sensitiveOperationsLimiter,
        private RequestStack $requestStack,
    ) {}

    /**
     * Apply rate limiting to login attempts
     */
    #[AsEventListener]
    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$this->isLoginRequest($request)) {
            return;
        }

        // Use IP address as identifier for login rate limiting
        $identifier = $request->getClientIp() ?? 'unknown';

        $limiter = $this->loginLimiter->create($identifier);
        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            $event->getAuthenticator()->onAuthenticationFailure(
                $request,
                new \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException(
                    'Too many login attempts. Please try again later.'
                )
            );
        }
    }

    /**
     * Apply rate limiting to API requests
     */
    #[AsEventListener]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Only apply rate limiting to API requests
        if (!$this->isApiRequest($request)) {
            return;
        }

        // Use IP address as identifier for API rate limiting
        $identifier = $request->getClientIp() ?? 'unknown';

        // For authenticated users, use their ID as identifier
        $user = $request->getUser();
        if ($user) {
            $identifier = $user;
        }

        // Check if this is a sensitive operation
        if ($this->isSensitiveOperation($request)) {
            $limiter = $this->sensitiveOperationsLimiter->create($identifier);
        } else {
            $limiter = $this->apiLimiter->create($identifier);
        }

        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            $event->setResponse(new JsonResponse(
                ['error' => 'Rate limit exceeded. Please try again later.'],
                JsonResponse::HTTP_TOO_MANY_REQUESTS
            ));
        }
    }

    private function isLoginRequest(Request $request): bool
    {
        return $request->getPathInfo() === '/api/login_check' && $request->isMethod('POST');
    }

    private function isApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api');
    }

    private function isSensitiveOperation(Request $request): bool
    {
        // Define paths that are considered sensitive operations
        $sensitivePaths = [
            '/api/users',           // User management
            '/api/teams',           // Team management
            '/api/admin',           // Admin operations
            '/api/password-reset',  // Password reset
        ];

        $path = $request->getPathInfo();

        foreach ($sensitivePaths as $sensitivePath) {
            if (str_starts_with($path, $sensitivePath)) {
                return true;
            }
        }

        return false;
    }
}
