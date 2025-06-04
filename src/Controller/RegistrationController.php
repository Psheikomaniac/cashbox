<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\ValueObject\Email;
use App\ValueObject\PersonName;
use App\ValueObject\PhoneNumber;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Modern PHP 8.4 Registration Controller with enhanced security
 */
#[Route('/api')]
class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator
    ) {}

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json(['error' => 'Invalid JSON format'], Response::HTTP_BAD_REQUEST);
            }

            // Validate required fields
            $requiredFields = ['firstName', 'lastName', 'email', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->json(['error' => "Field '$field' is required"], Response::HTTP_BAD_REQUEST);
                }
            }

            // Check if user already exists
            $existingUser = $this->userRepository->findOneBy(['emailValue' => $data['email']]);
            if ($existingUser) {
                return $this->json(['error' => 'User with this email already exists'], Response::HTTP_CONFLICT);
            }

            // Create user
            $user = new User(
                name: new PersonName($data['firstName'], $data['lastName']),
                password: '', // Will be set below
                email: new Email($data['email']),
                phoneNumber: isset($data['phoneNumber']) ? new PhoneNumber($data['phoneNumber']) : null,
                roles: ['ROLE_USER']
            );

            // Hash password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);

            // Validate user
            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            // Save user
            $this->userRepository->save($user, true);

            return $this->json([
                'message' => 'User registered successfully',
                'userId' => $user->getId()->toString()
            ], Response::HTTP_CREATED);

        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Registration failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}