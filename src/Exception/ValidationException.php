<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class ValidationException extends ApiException
{
    protected int $statusCode = Response::HTTP_BAD_REQUEST;
    private array $errors;

    public function __construct(
        string $message = 'Validation error',
        array $errors = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $errors, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
