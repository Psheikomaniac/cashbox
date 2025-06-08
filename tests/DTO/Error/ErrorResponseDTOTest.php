<?php

namespace App\Tests\DTO\Error;

use App\DTO\Error\ErrorResponseDTO;
use PHPUnit\Framework\TestCase;

class ErrorResponseDTOTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $dto = new ErrorResponseDTO(
            false,
            'Error message',
            ['field' => 'Field error'],
            400,
            'bad_request',
            'test-trace-id'
        );

        $json = $dto->jsonSerialize();

        $this->assertFalse($json['success']);
        $this->assertEquals('Error message', $json['error']['message']);
        $this->assertEquals(400, $json['error']['code']);
        $this->assertEquals('bad_request', $json['error']['type']);
        $this->assertEquals('test-trace-id', $json['error']['trace_id']);
        $this->assertArrayHasKey('details', $json['error']);
        $this->assertEquals('Field error', $json['error']['details']['field']);
    }

    public function testJsonSerializeWithoutDetails(): void
    {
        $dto = new ErrorResponseDTO(
            false,
            'Error message',
            [],
            400,
            'bad_request',
            'test-trace-id'
        );

        $json = $dto->jsonSerialize();

        $this->assertFalse($json['success']);
        $this->assertEquals('Error message', $json['error']['message']);
        $this->assertEquals(400, $json['error']['code']);
        $this->assertEquals('bad_request', $json['error']['type']);
        $this->assertEquals('test-trace-id', $json['error']['trace_id']);
        $this->assertArrayNotHasKey('details', $json['error']);
    }

    public function testDefaultValues(): void
    {
        $dto = new ErrorResponseDTO();

        $json = $dto->jsonSerialize();

        $this->assertFalse($json['success']);
        $this->assertEquals('', $json['error']['message']);
        $this->assertEquals(500, $json['error']['code']);
        $this->assertEquals('server_error', $json['error']['type']);
        $this->assertArrayHasKey('trace_id', $json['error']);
        $this->assertArrayNotHasKey('details', $json['error']);
    }

    public function testErrorTypeFromCode(): void
    {
        $codes = [
            400 => 'bad_request',
            401 => 'unauthorized',
            403 => 'forbidden',
            404 => 'not_found',
            409 => 'conflict',
            422 => 'validation_error',
            429 => 'too_many_requests',
            500 => 'server_error',
            501 => 'server_error',
            503 => 'server_error',
        ];

        foreach ($codes as $code => $expectedType) {
            $dto = new ErrorResponseDTO(false, 'Error', [], $code);
            $json = $dto->jsonSerialize();
            $this->assertEquals($expectedType, $json['error']['type'], "Code $code should map to type $expectedType");
        }
    }

    public function testTraceIdGeneration(): void
    {
        $dto = new ErrorResponseDTO();
        $json = $dto->jsonSerialize();

        $this->assertArrayHasKey('trace_id', $json['error']);
        $this->assertNotEmpty($json['error']['trace_id']);
        $this->assertIsString($json['error']['trace_id']);
        $this->assertEquals(16, strlen($json['error']['trace_id']), "Trace ID should be 16 characters (8 bytes as hex)");
    }
}
