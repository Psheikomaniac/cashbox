<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception for unauthorized access errors (HTTP 401).
 */
class UnauthorizedException extends ApiException
{
    protected int $statusCode = Response::HTTP_UNAUTHORIZED;

    public function __construct(
        string $message = 'Unauthorized access',
        array $details = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $details, $code, $previous);
    }
}
