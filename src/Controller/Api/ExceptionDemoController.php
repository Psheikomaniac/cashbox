<?php

namespace App\Controller\Api;

use App\Exception\BadRequestException;
use App\Exception\ConflictException;
use App\Exception\ForbiddenException;
use App\Exception\NotFoundException;
use App\Exception\UnauthorizedException;
use App\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller to demonstrate the unified exception handling.
 *
 * This controller provides endpoints that throw different types of exceptions
 * to demonstrate how they are handled by the ApiExceptionListener.
 */
#[Route('/api/exception-demo')]
class ExceptionDemoController extends AbstractController
{
    /**
     * Demonstrates a successful response.
     */
    #[Route('/success', name: 'api_exception_demo_success', methods: ['GET'])]
    public function success(): JsonResponse
    {
        return $this->json([
            'success' => true,
            'message' => 'This is a successful response',
            'data' => [
                'timestamp' => new \DateTimeImmutable(),
            ],
        ]);
    }

    /**
     * Demonstrates a BadRequestException (400).
     */
    #[Route('/bad-request', name: 'api_exception_demo_bad_request', methods: ['GET'])]
    public function badRequest(): JsonResponse
    {
        throw new BadRequestException('This is a bad request', ['param' => 'Invalid parameter']);
    }

    /**
     * Demonstrates an UnauthorizedException (401).
     */
    #[Route('/unauthorized', name: 'api_exception_demo_unauthorized', methods: ['GET'])]
    public function unauthorized(): JsonResponse
    {
        throw new UnauthorizedException('You are not authenticated');
    }

    /**
     * Demonstrates a ForbiddenException (403).
     */
    #[Route('/forbidden', name: 'api_exception_demo_forbidden', methods: ['GET'])]
    public function forbidden(): JsonResponse
    {
        throw new ForbiddenException('You do not have permission to access this resource');
    }

    /**
     * Demonstrates a NotFoundException (404).
     */
    #[Route('/not-found', name: 'api_exception_demo_not_found', methods: ['GET'])]
    public function notFound(): JsonResponse
    {
        throw new NotFoundException('The requested resource was not found');
    }

    /**
     * Demonstrates a ConflictException (409).
     */
    #[Route('/conflict', name: 'api_exception_demo_conflict', methods: ['GET'])]
    public function conflict(): JsonResponse
    {
        throw new ConflictException('The request conflicts with the current state of the resource');
    }

    /**
     * Demonstrates a ValidationException (400).
     */
    #[Route('/validation', name: 'api_exception_demo_validation', methods: ['GET'])]
    public function validation(): JsonResponse
    {
        throw new ValidationException('Validation failed', [
            'name' => 'Name is required',
            'email' => 'Email is invalid',
        ]);
    }

    /**
     * Demonstrates an uncaught exception (500).
     */
    #[Route('/server-error', name: 'api_exception_demo_server_error', methods: ['GET'])]
    public function serverError(): JsonResponse
    {
        throw new \RuntimeException('An unexpected error occurred');
    }
}
