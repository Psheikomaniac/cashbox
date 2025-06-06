<?php

namespace App\Controller;

use App\DTO\Auth\LoginRequestDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\ValueObject\Email;
use App\ValueObject\PersonName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}

    #[Route('/login_check', name: 'api_login_check', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Diese Methode wird nie aufgerufen, da der JWT-Authentifizierungs-Handler
        // die Anfrage abfängt und den Token generiert
        throw new \RuntimeException('Diese Methode sollte nicht aufgerufen werden');
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            // Request in DTO deserialisieren und validieren
            $loginRequest = $this->serializer->deserialize($request->getContent(), LoginRequestDTO::class, 'json');
            $errors = $this->validator->validate($loginRequest);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            // Prüfen, ob Benutzer bereits existiert
            $existingUser = $this->userRepository->findOneBy(['emailValue' => $loginRequest->email]);
            if ($existingUser) {
                return $this->json(['error' => 'Benutzer mit dieser E-Mail existiert bereits'], Response::HTTP_CONFLICT);
            }

            // Daten aus Request extrahieren
            $data = json_decode($request->getContent(), true);

            // Neue Benutzerentität erstellen
            $user = new User(
                new PersonName($data['firstName'] ?? '', $data['lastName'] ?? ''),
                new Email($loginRequest->email)
            );

            // Passwort hashen und setzen
            $hashedPassword = $this->passwordHasher->hashPassword($user, $loginRequest->password);
            $user->setPassword($hashedPassword);

            // Benutzer speichern
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json(['message' => 'Benutzer erfolgreich registriert'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/me', name: 'api_user_info', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Nicht authentifiziert'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId()->toString(),
            'email' => $user->getEmail()?->getValue(),
            'name' => $user->getName()->getFullName(),
            'roles' => $user->getRoles()
        ]);
    }
}
