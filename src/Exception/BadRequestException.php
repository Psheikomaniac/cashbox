<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception for bad request errors (HTTP 400).
 */
class BadRequestException extends ApiException
{
    protected int $statusCode = Response::HTTP_BAD_REQUEST;

    public function __construct(
        string $message = 'Bad Request',
        array $details = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $details, $code, $previous);
    }
}
