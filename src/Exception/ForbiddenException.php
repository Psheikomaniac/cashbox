<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception for forbidden access errors (HTTP 403).
 */
class ForbiddenException extends ApiException
{
    protected int $statusCode = Response::HTTP_FORBIDDEN;

    public function __construct(
        string $message = 'Access forbidden',
        array $details = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $details, $code, $previous);
    }
}
