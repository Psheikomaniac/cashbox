<?php

namespace App\Controller;

use App\DTO\PenaltyTypeInputDTO;
use App\DTO\PenaltyTypeOutputDTO;
use App\Entity\PenaltyType;
use App\Enum\PenaltyTypeEnum;
use App\Repository\PenaltyTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
        $penaltyTypeDTOs = array_map(fn (PenaltyType $penaltyType) => PenaltyTypeOutputDTO::createFromEntity($penaltyType), $penaltyTypes);

        return $this->json($penaltyTypeDTOs);
    }

    #[Route('/drinks', methods: ['GET'])]
    public function getDrinks(): JsonResponse
    {
        $penaltyTypes = $this->penaltyTypeRepository->findDrinks();
        $penaltyTypeDTOs = array_map(fn (PenaltyType $penaltyType) => PenaltyTypeOutputDTO::createFromEntity($penaltyType), $penaltyTypes);

        return $this->json($penaltyTypeDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $penaltyType = $this->penaltyTypeRepository->find($id);

        if (!$penaltyType) {
            return $this->json(['message' => 'Penalty type not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(PenaltyTypeOutputDTO::createFromEntity($penaltyType));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Create and populate PenaltyTypeInputDTO
        $penaltyTypeInputDTO = new PenaltyTypeInputDTO();
        $penaltyTypeInputDTO->name = $data['name'] ?? '';
        $penaltyTypeInputDTO->description = $data['description'] ?? null;
        $penaltyTypeInputDTO->type = $data['type'] ?? '';
        $penaltyTypeInputDTO->active = $data['active'] ?? true;

        // Validate the DTO (optional, can be added later)

        try {
            $type = PenaltyTypeEnum::from($penaltyTypeInputDTO->type);
        } catch (\ValueError $e) {
            return $this->json(['message' => 'Invalid penalty type'], Response::HTTP_BAD_REQUEST);
        }

        $penaltyType = new PenaltyType();
        $penaltyType->setName($penaltyTypeInputDTO->name);
        $penaltyType->setDescription($penaltyTypeInputDTO->description);
        $penaltyType->setType($type);
        $penaltyType->setActive($penaltyTypeInputDTO->active);

        $errors = $this->validator->validate($penaltyType);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($penaltyType);
        $this->entityManager->flush();

        return $this->json(PenaltyTypeOutputDTO::createFromEntity($penaltyType), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $penaltyType = $this->penaltyTypeRepository->find($id);

        if (!$penaltyType) {
            return $this->json(['message' => 'Penalty type not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Create and populate PenaltyTypeInputDTO with only the fields that are present in the request
        $penaltyTypeInputDTO = new PenaltyTypeInputDTO();

        if (isset($data['name'])) {
            $penaltyTypeInputDTO->name = $data['name'];
            $penaltyType->setName($penaltyTypeInputDTO->name);
        }

        if (array_key_exists('description', $data)) {
            $penaltyTypeInputDTO->description = $data['description'];
            $penaltyType->setDescription($penaltyTypeInputDTO->description);
        }

        if (isset($data['type'])) {
            $penaltyTypeInputDTO->type = $data['type'];
            try {
                $type = PenaltyTypeEnum::from($penaltyTypeInputDTO->type);
                $penaltyType->setType($type);
            } catch (\ValueError $e) {
                return $this->json(['message' => 'Invalid penalty type'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['active'])) {
            $penaltyTypeInputDTO->active = $data['active'];
            $penaltyType->setActive($penaltyTypeInputDTO->active);
        }

        $errors = $this->validator->validate($penaltyType);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(PenaltyTypeOutputDTO::createFromEntity($penaltyType));
    }

}
