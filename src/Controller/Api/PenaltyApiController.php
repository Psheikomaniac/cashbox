<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Penalty;
use App\Repository\PenaltyRepository;
use App\Repository\UserRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/penalties', name: 'api_penalties_')]
class PenaltyApiController extends AbstractController
{
    public function __construct(
        private PenaltyRepository $penaltyRepository,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));
        $teamId = $request->query->get('team');
        $userId = $request->query->get('user');
        $status = $request->query->get('status');

        $filters = array_filter([
            'team' => $teamId,
            'user' => $userId,
            'status' => $status
        ]);

        $penalties = $this->penaltyRepository->findPaginated($filters, $page, $limit);
        $total = $this->penaltyRepository->countFiltered($filters);

        return new JsonResponse([
            'data' => $this->serializer->normalize($penalties, 'json', ['groups' => ['penalty:read']]),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);
        
        if (!$penalty) {
            return new JsonResponse(['error' => 'Penalty not found'], 404);
        }

        return new JsonResponse(
            $this->serializer->normalize($penalty, 'json', ['groups' => ['penalty:read']])
        );
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        // Validate required fields
        $requiredFields = ['user_id', 'penalty_type_id', 'reason'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['error' => "Missing required field: $field"], 400);
            }
        }

        $user = $this->userRepository->find($data['user_id']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        try {
            $penalty = new Penalty();
            $penalty->setUser($user);
            $penalty->setReason($data['reason']);
            $penalty->setAmount($data['amount'] ?? 0);
            
            if (isset($data['due_date'])) {
                $penalty->setDueDate(new \DateTimeImmutable($data['due_date']));
            }

            $violations = $this->validator->validate($penalty);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                return new JsonResponse(['errors' => $errors], 400);
            }

            $this->entityManager->persist($penalty);
            $this->entityManager->flush();

            return new JsonResponse(
                $this->serializer->normalize($penalty, 'json', ['groups' => ['penalty:read']]),
                201
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to create penalty: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/pay', name: 'pay', methods: ['POST'])]
    public function markAsPaid(string $id, Request $request): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);
        
        if (!$penalty) {
            return new JsonResponse(['error' => 'Penalty not found'], 404);
        }

        if ($penalty->isPaid()) {
            return new JsonResponse(['error' => 'Penalty is already paid'], 400);
        }

        try {
            $penalty->markAsPaid();
            $this->entityManager->flush();

            return new JsonResponse(
                $this->serializer->normalize($penalty, 'json', ['groups' => ['penalty:read']])
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}/archive', name: 'archive', methods: ['POST'])]
    public function archive(string $id): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);
        
        if (!$penalty) {
            return new JsonResponse(['error' => 'Penalty not found'], 404);
        }

        try {
            $penalty->archive();
            $this->entityManager->flush();

            return new JsonResponse(
                $this->serializer->normalize($penalty, 'json', ['groups' => ['penalty:read']])
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}