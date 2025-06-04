<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\BusinessLogicException;
use App\Exception\ResourceNotFoundException;
use App\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Modern PHP 8.4 Exception Listener with enhanced error handling
 */
final readonly class ExceptionListener
{
    public function __construct(
        private LoggerInterface $logger,
        private string $environment
    ) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle JSON API requests
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        // Log the exception
        $this->logException($exception, $request->getPathInfo());

        // Create standardized error response
        $response = $this->createErrorResponse($exception);
        $event->setResponse($response);
    }

    private function createErrorResponse(\Throwable $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $errorData = $this->getErrorData($exception, $statusCode);

        return new JsonResponse($errorData, $statusCode);
    }

    private function getStatusCode(\Throwable $exception): int
    {
        return match (true) {
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            $exception instanceof ValidationFailedException => Response::HTTP_BAD_REQUEST,
            $exception instanceof ValidationException => Response::HTTP_BAD_REQUEST,
            $exception instanceof BusinessLogicException => Response::HTTP_BAD_REQUEST,
            $exception instanceof \InvalidArgumentException => Response::HTTP_BAD_REQUEST,
            default => Response::HTTP_INTERNAL_SERVER_ERROR
        };
    }

    private function getErrorData(\Throwable $exception, int $statusCode): array
    {
        $errorData = [
            'error' => [
                'code' => $statusCode,
                'message' => $this->getErrorMessage($exception, $statusCode),
                'type' => $this->getErrorType($exception)
            ]
        ];

        // Add validation errors if applicable
        if ($exception instanceof ValidationFailedException) {
            $errorData['error']['violations'] = $this->formatValidationErrors(
                $exception->getViolations()
            );
        } elseif ($exception instanceof ValidationException) {
            $errorData['error']['violations'] = $exception->getViolations();
        }

        // Add debug information in development
        if ($this->environment === 'dev') {
            $errorData['debug'] = [
                'exception_class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => array_slice($exception->getTrace(), 0, 5) // Limit trace
            ];
        }

        return $errorData;
    }

    private function getErrorMessage(\Throwable $exception, int $statusCode): string
    {
        return match (true) {
            $exception instanceof ResourceNotFoundException => $exception->getMessage(),
            $exception instanceof ValidationException => $exception->getMessage(),
            $exception instanceof BusinessLogicException => $exception->getMessage(),
            $exception instanceof NotFoundHttpException => 'Resource not found',
            $exception instanceof UnauthorizedHttpException => 'Authentication required',
            $exception instanceof ValidationFailedException => 'Validation failed',
            $exception instanceof \InvalidArgumentException => $exception->getMessage(),
            $statusCode === Response::HTTP_FORBIDDEN => 'Access denied',
            $statusCode === Response::HTTP_TOO_MANY_REQUESTS => 'Too many requests',
            $statusCode >= 500 => 'Internal server error',
            default => $exception->getMessage()
        };
    }

    private function getErrorType(\Throwable $exception): string
    {
        return match (true) {
            $exception instanceof ResourceNotFoundException => 'RESOURCE_NOT_FOUND',
            $exception instanceof ValidationException => 'VALIDATION_ERROR',
            $exception instanceof BusinessLogicException => 'BUSINESS_LOGIC_ERROR',
            $exception instanceof NotFoundHttpException => 'NOT_FOUND',
            $exception instanceof UnauthorizedHttpException => 'UNAUTHORIZED',
            $exception instanceof ValidationFailedException => 'VALIDATION_FAILED',
            $exception instanceof \InvalidArgumentException => 'INVALID_INPUT',
            $exception instanceof HttpExceptionInterface && $exception->getStatusCode() === 403 => 'FORBIDDEN',
            $exception instanceof HttpExceptionInterface && $exception->getStatusCode() === 429 => 'RATE_LIMIT_EXCEEDED',
            default => 'INTERNAL_ERROR'
        };
    }

    private function formatValidationErrors(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
                'invalid_value' => $violation->getInvalidValue()
            ];
        }
        return $errors;
    }

    private function logException(\Throwable $exception, string $path): void
    {
        $context = [
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'path' => $path
        ];

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            if ($statusCode >= 500) {
                $this->logger->error($exception->getMessage(), $context);
            } elseif ($statusCode >= 400) {
                $this->logger->warning($exception->getMessage(), $context);
            }
        } else {
            $this->logger->error($exception->getMessage(), $context);
        }
    }
}