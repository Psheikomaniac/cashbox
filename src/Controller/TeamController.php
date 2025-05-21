<?php

namespace App\Controller;

use App\DTO\TeamDTO;
use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
        $teamDTOs = array_map(fn (Team $team) => TeamDTO::createFromEntity($team), $teams);

        return $this->json($teamDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $team = $this->teamRepository->find($id);

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(TeamDTO::createFromEntity($team));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $team = new Team();
        $team->setName($data['name']);
        $team->setExternalId($data['externalId']);
        $team->setActive($data['active'] ?? true);
        $team->setCreatedAt(new \DateTimeImmutable());
        $team->setUpdatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($team);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($team);
        $this->entityManager->flush();

        return $this->json(TeamDTO::createFromEntity($team), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $team = $this->teamRepository->find($id);

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $team->setName($data['name']);
        }

        if (isset($data['externalId'])) {
            $team->setExternalId($data['externalId']);
        }

        if (isset($data['active'])) {
            $team->setActive($data['active']);
        }

        $errors = $this->validator->validate($team);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(TeamDTO::createFromEntity($team));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $team = $this->teamRepository->find($id);

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($team);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
