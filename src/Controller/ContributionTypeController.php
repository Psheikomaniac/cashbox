<?php

namespace App\Controller;

use App\DTO\ContributionTypeOutputDTO;
use App\Entity\ContributionType;
use App\Enum\RecurrencePatternEnum;
use App\Repository\ContributionTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/contribution-types')]
class ContributionTypeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContributionTypeRepository $contributionTypeRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $types = $this->contributionTypeRepository->findAll();
        $typeDTOs = array_map(
            fn (ContributionType $type) => ContributionTypeOutputDTO::fromEntity($type),
            $types
        );

        return $this->json($typeDTOs);
    }

    #[Route('/active', methods: ['GET'])]
    public function getActive(): JsonResponse
    {
        $types = $this->contributionTypeRepository->findActive();
        $typeDTOs = array_map(
            fn (ContributionType $type) => ContributionTypeOutputDTO::fromEntity($type),
            $types
        );

        return $this->json($typeDTOs);
    }

    #[Route('/recurring', methods: ['GET'])]
    public function getRecurring(): JsonResponse
    {
        $types = $this->contributionTypeRepository->findRecurring();
        $typeDTOs = array_map(
            fn (ContributionType $type) => ContributionTypeOutputDTO::fromEntity($type),
            $types
        );

        return $this->json($typeDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $type = $this->contributionTypeRepository->find($id);

        if (!$type) {
            return $this->json(['message' => 'Contribution type not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(ContributionTypeOutputDTO::fromEntity($type));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $recurrencePattern = isset($data['recurrencePattern']) ?
            RecurrencePatternEnum::tryFrom($data['recurrencePattern']) : null;

        $type = new ContributionType(
            name: $data['name'],
            description: $data['description'] ?? null,
            recurring: $data['recurring'] ?? false,
            recurrencePattern: $recurrencePattern
        );

        $errors = $this->validator->validate($type);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($type);
        $this->entityManager->flush();

        return $this->json(ContributionTypeOutputDTO::fromEntity($type), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $type = $this->contributionTypeRepository->find($id);

        if (!$type) {
            return $this->json(['message' => 'Contribution type not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Update core properties through domain method
        if (isset($data['name']) || array_key_exists('description', $data) || isset($data['recurring']) || array_key_exists('recurrencePattern', $data)) {
            $recurrencePattern = array_key_exists('recurrencePattern', $data) ?
                (isset($data['recurrencePattern']) ? RecurrencePatternEnum::tryFrom($data['recurrencePattern']) : null) :
                $type->getRecurrencePattern();

            $type->update(
                name: $data['name'] ?? $type->getName(),
                description: array_key_exists('description', $data) ? $data['description'] : $type->getDescription(),
                recurring: $data['recurring'] ?? $type->isRecurring(),
                recurrencePattern: $recurrencePattern
            );
        }

        // Handle activation/deactivation separately
        if (isset($data['active'])) {
            if ($data['active'] && !$type->isActive()) {
                $type->activate();
            } elseif (!$data['active'] && $type->isActive()) {
                $type->deactivate();
            }
        }

        $errors = $this->validator->validate($type);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(ContributionTypeOutputDTO::fromEntity($type));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $type = $this->contributionTypeRepository->find($id);

        if (!$type) {
            return $this->json(['message' => 'Contribution type not found'], Response::HTTP_NOT_FOUND);
        }

        $type->deactivate();
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
