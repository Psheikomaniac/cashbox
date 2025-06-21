<?php

namespace App\EventListener;

use App\DTO\Error\ErrorResponseDTO;
use App\Exception\ApiException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Global exception handler for API exceptions.
 *
 * This listener catches all exceptions thrown during request handling and formats them
 * into a consistent JSON response format.
 */
#[AsEventListener(event: 'kernel.exception', priority: 10)]
readonly class ApiExceptionListener
{
    public function __construct(
        private SerializerInterface $serializer
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle API requests (those starting with /api)
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        // Create the error response
        $statusCode = 500;
        $errorResponse = ErrorResponseDTO::create(
            success: false,
            message: 'Internal Server Error',
            details: [],
            code: $statusCode
        );

        // Handle ApiException
        if ($exception instanceof ApiException) {
            $statusCode = $exception->getStatusCode();
            $errorResponse = ErrorResponseDTO::create(
                success: false,
                message: $exception->getMessage(),
                details: $exception->getDetails(),
                code: $statusCode
            );
        }
        // Handle Symfony HTTP exceptions
        elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $errorResponse = ErrorResponseDTO::create(
                success: false,
                message: $exception->getMessage(),
                details: [],
                code: $statusCode
            );
        }
        // Handle other exceptions in production
        elseif ($_ENV['APP_ENV'] === 'prod') {
            // In production, don't expose exception details
            $errorResponse = ErrorResponseDTO::create(
                success: false,
                message: 'Internal Server Error',
                details: [],
                code: $statusCode
            );
        }
        // Handle other exceptions in development
        else {
            // In development, include exception details
            $errorResponse = ErrorResponseDTO::create(
                success: false,
                message: $exception->getMessage(),
                details: [
                    'class' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ],
                code: $statusCode
            );
        }

        // Create the JSON response
        $jsonResponse = new JsonResponse(
            $errorResponse->jsonSerialize(),
            $statusCode
        );

        // Set the response on the event
        $event->setResponse($jsonResponse);
    }
}
