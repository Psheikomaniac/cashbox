<?php

namespace App\Controller;

use App\DTO\UserInputDTO;
use App\DTO\UserOutputDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        $userDTOs = array_map(fn (User $user) => UserOutputDTO::createFromEntity($user), $users);

        return $this->json($userDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(UserOutputDTO::createFromEntity($user));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Create and populate UserInputDTO
        $userInputDTO = new UserInputDTO();
        $userInputDTO->firstName = $data['firstName'] ?? '';
        $userInputDTO->lastName = $data['lastName'] ?? '';
        $userInputDTO->email = $data['email'] ?? null;
        $userInputDTO->phoneNumber = $data['phoneNumber'] ?? null;
        $userInputDTO->active = $data['active'] ?? true;

        // Validate the DTO (optional, can be added later)

        // Create and populate User entity from DTO
        $user = new User();
        $user->setFirstName($userInputDTO->firstName);
        $user->setLastName($userInputDTO->lastName);
        $user->setEmail($userInputDTO->email);
        $user->setPhoneNumber($userInputDTO->phoneNumber);
        $user->setActive($userInputDTO->active);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(UserOutputDTO::createFromEntity($user), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Create and populate UserInputDTO with only the fields that are present in the request
        $userInputDTO = new UserInputDTO();

        if (isset($data['firstName'])) {
            $userInputDTO->firstName = $data['firstName'];
            $user->setFirstName($userInputDTO->firstName);
        }

        if (isset($data['lastName'])) {
            $userInputDTO->lastName = $data['lastName'];
            $user->setLastName($userInputDTO->lastName);
        }

        if (array_key_exists('email', $data)) {
            $userInputDTO->email = $data['email'];
            $user->setEmail($userInputDTO->email);
        }

        if (array_key_exists('phoneNumber', $data)) {
            $userInputDTO->phoneNumber = $data['phoneNumber'];
            $user->setPhoneNumber($userInputDTO->phoneNumber);
        }

        if (isset($data['active'])) {
            $userInputDTO->active = $data['active'];
            $user->setActive($userInputDTO->active);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(UserOutputDTO::createFromEntity($user));
    }

}
