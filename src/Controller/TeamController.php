<?php

namespace App\Controller;

use App\DTO\TeamInputDTO;
use App\DTO\TeamOutputDTO;
use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/teams')]
class TeamController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TeamRepository $teamRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $teams = $this->teamRepository->findAll();
        $teamDTOs = array_map(fn (Team $team) => TeamOutputDTO::createFromEntity($team), $teams);

        return $this->json($teamDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $team = $this->teamRepository->find($id);

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(TeamOutputDTO::createFromEntity($team));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Create and populate TeamInputDTO
        $teamInputDTO = new TeamInputDTO();
        $teamInputDTO->name = $data['name'] ?? '';
        $teamInputDTO->externalId = $data['externalId'] ?? null;
        $teamInputDTO->active = $data['active'] ?? true;

        // Validate the DTO (optional, can be added later)

        // Create and populate Team entity from DTO
        $team = new Team();
        $team->setName($teamInputDTO->name);
        if ($teamInputDTO->externalId !== null) {
            $team->setExternalId($teamInputDTO->externalId);
        }
        $team->setActive($teamInputDTO->active);

        $errors = $this->validator->validate($team);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($team);
        $this->entityManager->flush();

        return $this->json(TeamOutputDTO::createFromEntity($team), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $team = $this->teamRepository->find($id);

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Create and populate TeamInputDTO with only the fields that are present in the request
        $teamInputDTO = new TeamInputDTO();

        if (isset($data['name'])) {
            $teamInputDTO->name = $data['name'];
            $team->setName($teamInputDTO->name);
        }

        if (isset($data['externalId'])) {
            $teamInputDTO->externalId = $data['externalId'];
            $team->setExternalId($teamInputDTO->externalId);
        }

        if (isset($data['active'])) {
            $teamInputDTO->active = $data['active'];
            $team->setActive($teamInputDTO->active);
        }

        $errors = $this->validator->validate($team);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(TeamOutputDTO::createFromEntity($team));
    }

}
