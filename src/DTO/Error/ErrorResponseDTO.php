<?php

namespace App\DTO\Error;

use App\DTO\AbstractResponseDTO;
use JsonSerializable;

/**
 * DTO for standardized error responses.
 *
 * This DTO is used to format error responses in a consistent way across the API.
 */
class ErrorResponseDTO implements JsonSerializable
{
    public readonly bool $success;
    public readonly string $message;
    public readonly array $details;
    public readonly int $code;
    public readonly string $type;
    public readonly string $traceId;

    public function __construct(
        bool $success = false,
        string $message = '',
        array $details = [],
        int $code = 500,
        ?string $type = null,
        ?string $traceId = null
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->details = $details;
        $this->code = $code;
        $this->traceId = $traceId ?? bin2hex(random_bytes(8));
        $this->type = $type ?? $this->getErrorTypeFromCode($code);
    }

    /**
     * Maps HTTP status codes to error types.
     */
    private function getErrorTypeFromCode(int $code): string
    {
        return match ($code) {
            400 => 'bad_request',
            401 => 'unauthorized',
            403 => 'forbidden',
            404 => 'not_found',
            409 => 'conflict',
            422 => 'validation_error',
            429 => 'too_many_requests',
            default => 'server_error',
        };
    }

    /**
     * Implements JsonSerializable to control the JSON serialization.
     */
    public function jsonSerialize(): array
    {
        $result = [
            'success' => $this->success,
            'error' => [
                'message' => $this->message,
                'code' => $this->code,
                'type' => $this->type,
                'trace_id' => $this->traceId,
            ],
        ];

        // Only include details if there are any
        if (!empty($this->details)) {
            $result['error']['details'] = $this->details;
        }

        return $result;
    }
}
