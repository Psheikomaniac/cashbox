<?php

namespace App\Tests\EventListener;

use App\DTO\Error\ErrorResponseDTO;
use App\EventListener\ApiExceptionListener;
use App\Exception\BadRequestException;
use App\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class ApiExceptionListenerTest extends TestCase
{
    private SerializerInterface $serializer;
    private ApiExceptionListener $listener;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->listener = new ApiExceptionListener($this->serializer);
    }

    public function testNonApiRequestsAreIgnored(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $request->server->set('REQUEST_URI', '/non-api/path');

        $exception = new \Exception('Test exception');
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->listener->__invoke($event);

        $this->assertNull($event->getResponse());
    }

    public function testApiExceptionIsHandled(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $request->server->set('REQUEST_URI', '/api/test');

        $exception = new NotFoundException('Resource not found');
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->listener->__invoke($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals(404, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Resource not found', $content['error']['message']);
        $this->assertEquals(404, $content['error']['code']);
        $this->assertEquals('not_found', $content['error']['type']);
        $this->assertArrayHasKey('trace_id', $content['error']);
    }

    public function testSymfonyHttpExceptionIsHandled(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $request->server->set('REQUEST_URI', '/api/test');

        $exception = new NotFoundHttpException('Route not found');
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->listener->__invoke($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals(404, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Route not found', $content['error']['message']);
        $this->assertEquals(404, $content['error']['code']);
    }

    public function testGenericExceptionIsHandledInDevMode(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $request->server->set('REQUEST_URI', '/api/test');

        $exception = new \RuntimeException('Something went wrong');
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        // Set APP_ENV to dev
        $_ENV['APP_ENV'] = 'dev';

        $this->listener->__invoke($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals(500, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Something went wrong', $content['error']['message']);
        $this->assertEquals(500, $content['error']['code']);
        $this->assertArrayHasKey('details', $content['error']);
        $this->assertArrayHasKey('class', $content['error']['details']);
        $this->assertArrayHasKey('file', $content['error']['details']);
        $this->assertArrayHasKey('line', $content['error']['details']);
        $this->assertArrayHasKey('trace', $content['error']['details']);
    }

    public function testGenericExceptionIsHandledInProdMode(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $request->server->set('REQUEST_URI', '/api/test');

        $exception = new \RuntimeException('Something went wrong');
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        // Set APP_ENV to prod
        $_ENV['APP_ENV'] = 'prod';

        $this->listener->__invoke($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals(500, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Internal Server Error', $content['error']['message']);
        $this->assertEquals(500, $content['error']['code']);
        $this->assertArrayNotHasKey('details', $content['error']);
    }

    public function testBadRequestExceptionWithDetails(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $request->server->set('REQUEST_URI', '/api/test');

        $exception = new BadRequestException('Invalid input', ['field' => 'This field is required']);
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->listener->__invoke($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals(400, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Invalid input', $content['error']['message']);
        $this->assertEquals(400, $content['error']['code']);
        $this->assertEquals('bad_request', $content['error']['type']);
        $this->assertArrayHasKey('details', $content['error']);
        $this->assertEquals('This field is required', $content['error']['details']['field']);
    }
}
