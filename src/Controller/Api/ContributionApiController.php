<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Contribution;
use App\Repository\ContributionRepository;
use App\Service\ContributionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/contributions', name: 'api_contributions_')]
class ContributionApiController extends AbstractController
{
    public function __construct(
        private ContributionRepository $contributionRepository,
        private ContributionService $contributionService,
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

        $contributions = $this->contributionRepository->findPaginated($filters, $page, $limit);
        $total = $this->contributionRepository->countFiltered($filters);

        return new JsonResponse([
            'data' => $this->serializer->normalize($contributions, 'json', ['groups' => ['contribution:read']]),
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
        $contribution = $this->contributionRepository->find($id);
        
        if (!$contribution) {
            return new JsonResponse(['error' => 'Contribution not found'], 404);
        }

        return new JsonResponse(
            $this->serializer->normalize($contribution, 'json', ['groups' => ['contribution:read']])
        );
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        try {
            $contribution = $this->contributionService->createContribution($data);
            
            return new JsonResponse(
                $this->serializer->normalize($contribution, 'json', ['groups' => ['contribution:read']]),
                201
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}/pay', name: 'pay', methods: ['POST'])]
    public function markAsPaid(string $id, Request $request): JsonResponse
    {
        $contribution = $this->contributionRepository->find($id);
        
        if (!$contribution) {
            return new JsonResponse(['error' => 'Contribution not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        try {
            $this->contributionService->markAsPaid($contribution, $data);
            
            return new JsonResponse(
                $this->serializer->normalize($contribution, 'json', ['groups' => ['contribution:read']])
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}/calculate', name: 'calculate', methods: ['GET'])]
    public function calculate(string $id): JsonResponse
    {
        $contribution = $this->contributionRepository->find($id);
        
        if (!$contribution) {
            return new JsonResponse(['error' => 'Contribution not found'], 404);
        }

        $calculation = $this->contributionService->calculateAmount($contribution);
        
        return new JsonResponse([
            'contribution_id' => $contribution->getId(),
            'base_amount' => $calculation['base_amount'],
            'penalties' => $calculation['penalties'],
            'total_amount' => $calculation['total_amount'],
            'due_date' => $contribution->getDueDate()?->format('Y-m-d'),
            'days_overdue' => $calculation['days_overdue']
        ]);
    }
}