<?php

namespace App\Controller;

use App\DTO\ContributionTypeOutputDTO;
use App\Entity\ContributionType;
use App\Repository\ContributionTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/contribution-types')]
class ContributionTypeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContributionTypeRepository $contributionTypeRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $types = $this->contributionTypeRepository->findAll();
        $typeDTOs = array_map(
            fn (ContributionType $type) => ContributionTypeOutputDTO::createFromEntity($type),
            $types
        );

        return $this->json($typeDTOs);
    }

    #[Route('/active', methods: ['GET'])]
    public function getActive(): JsonResponse
    {
        $types = $this->contributionTypeRepository->findActive();
        $typeDTOs = array_map(
            fn (ContributionType $type) => ContributionTypeOutputDTO::createFromEntity($type),
            $types
        );

        return $this->json($typeDTOs);
    }

    #[Route('/recurring', methods: ['GET'])]
    public function getRecurring(): JsonResponse
    {
        $types = $this->contributionTypeRepository->findRecurring();
        $typeDTOs = array_map(
            fn (ContributionType $type) => ContributionTypeOutputDTO::createFromEntity($type),
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

        return $this->json(ContributionTypeOutputDTO::createFromEntity($type));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $type = new ContributionType();
        $type->setName($data['name']);

        if (isset($data['description'])) {
            $type->setDescription($data['description']);
        }

        $type->setRecurring($data['recurring'] ?? false);

        if (isset($data['recurrencePattern'])) {
            $type->setRecurrencePattern($data['recurrencePattern']);
        }

        $errors = $this->validator->validate($type);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($type);
        $this->entityManager->flush();

        return $this->json(ContributionTypeOutputDTO::createFromEntity($type), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $type = $this->contributionTypeRepository->find($id);

        if (!$type) {
            return $this->json(['message' => 'Contribution type not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $type->setName($data['name']);
        }

        if (array_key_exists('description', $data)) {
            $type->setDescription($data['description']);
        }

        if (isset($data['recurring'])) {
            $type->setRecurring($data['recurring']);
        }

        if (array_key_exists('recurrencePattern', $data)) {
            $type->setRecurrencePattern($data['recurrencePattern']);
        }

        if (isset($data['active'])) {
            $type->setActive($data['active']);
        }

        $errors = $this->validator->validate($type);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(ContributionTypeOutputDTO::createFromEntity($type));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $type = $this->contributionTypeRepository->find($id);

        if (!$type) {
            return $this->json(['message' => 'Contribution type not found'], Response::HTTP_NOT_FOUND);
        }

        $type->setActive(false);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
