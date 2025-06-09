<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception for resource not found errors (HTTP 404).
 */
class NotFoundException extends ApiException
{
    protected int $statusCode = Response::HTTP_NOT_FOUND;

    public function __construct(
        string $message = 'Resource not found',
        array $details = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $details, $code, $previous);
    }
}
