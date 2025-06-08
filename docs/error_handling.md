# Error Handling Guide

This document describes the unified error handling approach implemented in the Cashbox application.

## Overview

The application uses a centralized exception handling mechanism to ensure consistent error responses across all API endpoints. This approach:

1. Provides a consistent error response format
2. Reduces code duplication in controllers
3. Simplifies error handling by using exceptions
4. Improves maintainability by centralizing error handling logic

## Error Response Format

All API error responses follow this standardized JSON format:

```json
{
  "success": false,
  "error": {
    "message": "Human-readable error message",
    "code": 400,
    "type": "bad_request",
    "trace_id": "a1b2c3d4e5f6",
    "details": {
      "field1": "Error message for field1",
      "field2": "Error message for field2"
    }
  }
}
```

- `success`: Always `false` for error responses
- `error`: Contains the error details
  - `message`: A human-readable error message
  - `code`: The HTTP status code
  - `type`: A machine-readable error type (e.g., `bad_request`, `not_found`)
  - `trace_id`: A unique identifier for the error, useful for troubleshooting
  - `details`: Optional field containing additional error details, often used for validation errors

## Exception Hierarchy

The application uses a hierarchy of exceptions to represent different types of errors:

- `ApiException` (abstract): Base class for all API exceptions
  - `BadRequestException`: For invalid request data (400)
  - `UnauthorizedException`: For authentication failures (401)
  - `ForbiddenException`: For authorization failures (403)
  - `NotFoundException`: For resources that don't exist (404)
  - `ConflictException`: For conflicts with the current state (409)
  - `ValidationException`: For validation errors (400)

## How to Use

### Throwing Exceptions

Instead of manually creating error responses in your controllers, throw the appropriate exception:

```php
// Instead of this:
if (!$resource) {
    return $this->json(['error' => 'Resource not found'], 404);
}

// Do this:
if (!$resource) {
    throw new NotFoundException('Resource not found');
}
```

### Validation Errors

For validation errors, use the `ValidationException` with detailed error messages:

```php
$errors = $validator->validate($entity);
if (count($errors) > 0) {
    $errorMessages = [];
    foreach ($errors as $error) {
        $errorMessages[$error->getPropertyPath()] = $error->getMessage();
    }
    throw new ValidationException('Validation failed', $errorMessages);
}
```

### Using the RequestValidator Service

The `RequestValidator` service simplifies request validation and automatically throws a `ValidationException` if validation fails:

```php
public function create(Request $request): JsonResponse
{
    // This will throw a ValidationException if validation fails
    $dto = $this->requestValidator->validateRequest($request, CreateResourceDTO::class);

    // Process the validated DTO
    // ...
}
```

## Example Controller

See `src/Controller/Api/ExceptionDemoController.php` for examples of how to use the different exception types.

## Testing

You can test the error handling by visiting these endpoints:

- `/api/exception-demo/success`: A successful response
- `/api/exception-demo/bad-request`: A 400 Bad Request error
- `/api/exception-demo/unauthorized`: A 401 Unauthorized error
- `/api/exception-demo/forbidden`: A 403 Forbidden error
- `/api/exception-demo/not-found`: A 404 Not Found error
- `/api/exception-demo/conflict`: A 409 Conflict error
- `/api/exception-demo/validation`: A validation error with field details
- `/api/exception-demo/server-error`: A 500 Server Error

## Implementation Details

The error handling is implemented using:

1. A hierarchy of exception classes in `src/Exception/`
2. An event listener (`ApiExceptionListener`) that catches exceptions and formats them into consistent responses
3. A DTO class (`ErrorResponseDTO`) that defines the error response format

## Best Practices

1. Always throw the most specific exception type for the error
2. Include a clear, human-readable error message
3. For validation errors, include detailed error messages for each field
4. Don't catch exceptions in controllers unless you need to perform additional logic
5. Don't return error responses directly from controllers; throw exceptions instead
