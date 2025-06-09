<?php

namespace App\Exception;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base exception class for API-related exceptions.
 */
abstract class ApiException extends RuntimeException
{
    protected int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
    protected array $details = [];

    public function __construct(
        string $message = '',
        array $details = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
