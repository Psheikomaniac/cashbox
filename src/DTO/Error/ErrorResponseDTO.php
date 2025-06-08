<?php

namespace App\DTO\Error;

use App\DTO\AbstractResponseDTO;
use JsonSerializable;

/**
 * DTO for standardized error responses.
 *
 * This DTO is used to format error responses in a consistent way across the API.
 */
readonly class ErrorResponseDTO implements JsonSerializable
{
    /**
     * Private constructor to ensure all properties are initialized correctly.
     * Use the static create() method instead.
     */
    private function __construct(
        public bool $success,
        public string $message,
        public array $details,
        public int $code,
        public string $type,
        public string $traceId
    ) {
    }

    /**
     * Creates a new ErrorResponseDTO with all properties initialized correctly.
     */
    public static function create(
        bool $success = false,
        string $message = '',
        array $details = [],
        int $code = 500,
        ?string $type = null,
        ?string $traceId = null
    ): self {
        $actualType = $type ?? self::getErrorTypeFromCode($code);
        $actualTraceId = $traceId ?? bin2hex(random_bytes(8));

        return new self(
            $success,
            $message,
            $details,
            $code,
            $actualType,
            $actualTraceId
        );
    }

    /**
     * Maps HTTP status codes to error types.
     */
    private static function getErrorTypeFromCode(int $code): string
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
