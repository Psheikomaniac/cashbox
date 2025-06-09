<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception for conflict errors (HTTP 409).
 */
class ConflictException extends ApiException
{
    protected int $statusCode = Response::HTTP_CONFLICT;

    public function __construct(
        string $message = 'Conflict with current state of the resource',
        array $details = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $details, $code, $previous);
    }
}
