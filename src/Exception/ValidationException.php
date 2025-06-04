<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Modern PHP 8.4 Exception for validation errors
 */
final class ValidationException extends BadRequestHttpException
{
    public function __construct(
        string $message = 'Validation failed',
        private readonly array $violations = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $previous);
    }

    public function getViolations(): array
    {
        return $this->violations;
    }

    public static function fromArray(array $violations): self
    {
        return new self('Validation failed', $violations);
    }

    public static function forField(string $field, string $message): self
    {
        return new self(
            "Validation failed for field '$field'",
            [['property' => $field, 'message' => $message]]
        );
    }
}