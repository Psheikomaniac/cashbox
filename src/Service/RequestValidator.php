<?php

namespace App\Service;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestValidator
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}

    /**
     * Deserializes and validates a request into a DTO
     *
     * @template T of object
     * @param Request $request The request to validate
     * @param class-string<T> $dtoClass The DTO class
     * @return T The validated DTO object
     * @throws ValidationException If validation fails
     */
    public function validateRequest(Request $request, string $dtoClass): object
    {
        $dto = $this->serializer->deserialize(
            data: $request->getContent(),
            type: $dtoClass,
            format: 'json'
        );

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            // Extract error messages using first-class callable syntax
            $getErrorMessage = fn($error) => $error->getMessage();
            $getPropertyPath = fn($error) => $error->getPropertyPath();

            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$getPropertyPath($error)] = $getErrorMessage($error);
            }

            throw new ValidationException(
                message: 'Validation error',
                details: $errorMessages
            );
        }

        return $dto;
    }
}
