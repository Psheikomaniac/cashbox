<?php

namespace App\Controller;

use App\DTO\PenaltyTypeDTO;
use App\Entity\PenaltyType;
use App\Enum\PenaltyTypeEnum;
use App\Repository\PenaltyTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/penalty-types')]
class PenaltyTypeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PenaltyTypeRepository $penaltyTypeRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $penaltyTypes = $this->penaltyTypeRepository->findAll();
        $penaltyTypeDTOs = array_map(fn (PenaltyType $penaltyType) => PenaltyTypeDTO::createFromEntity($penaltyType), $penaltyTypes);

        return $this->json($penaltyTypeDTOs);
    }

    #[Route('/drinks', methods: ['GET'])]
    public function getDrinks(): JsonResponse
    {
        $penaltyTypes = $this->penaltyTypeRepository->findDrinks();
        $penaltyTypeDTOs = array_map(fn (PenaltyType $penaltyType) => PenaltyTypeDTO::createFromEntity($penaltyType), $penaltyTypes);

        return $this->json($penaltyTypeDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $penaltyType = $this->penaltyTypeRepository->find($id);

        if (!$penaltyType) {
            return $this->json(['message' => 'Penalty type not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(PenaltyTypeDTO::createFromEntity($penaltyType));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $type = PenaltyTypeEnum::from($data['type']);
        } catch (\ValueError $e) {
            return $this->json(['message' => 'Invalid penalty type'], Response::HTTP_BAD_REQUEST);
        }

        $penaltyType = new PenaltyType();
        $penaltyType->setName($data['name']);
        $penaltyType->setDescription($data['description'] ?? null);
        $penaltyType->setType($type);
        $penaltyType->setActive($data['active'] ?? true);
        $penaltyType->setCreatedAt(new \DateTimeImmutable());
        $penaltyType->setUpdatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($penaltyType);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($penaltyType);
        $this->entityManager->flush();

        return $this->json(PenaltyTypeDTO::createFromEntity($penaltyType), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $penaltyType = $this->penaltyTypeRepository->find($id);

        if (!$penaltyType) {
            return $this->json(['message' => 'Penalty type not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $penaltyType->setName($data['name']);
        }

        if (array_key_exists('description', $data)) {
            $penaltyType->setDescription($data['description']);
        }

        if (isset($data['type'])) {
            try {
                $type = PenaltyTypeEnum::from($data['type']);
                $penaltyType->setType($type);
            } catch (\ValueError $e) {
                return $this->json(['message' => 'Invalid penalty type'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['active'])) {
            $penaltyType->setActive($data['active']);
        }

        $errors = $this->validator->validate($penaltyType);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(PenaltyTypeDTO::createFromEntity($penaltyType));
    }

}
