<?php

namespace App\Infrastructure\Api\Controller;

use App\Application\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/teams')]
class TeamController extends AbstractController
{
    public function __construct(
        private readonly TeamService $teamService
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty($data['name'])) {
            return $this->json(['error' => 'Team name is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $teamId = $this->teamService->createTeam(
                $data['name'],
                $data['description'] ?? '',
                $data['active'] ?? true
            );

            return $this->json(['id' => $teamId], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred while creating the team'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getById(string $id): JsonResponse
    {
        $team = $this->teamService->getTeamById($id);

        if ($team === null) {
            return $this->json(['error' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($team);
    }
}
