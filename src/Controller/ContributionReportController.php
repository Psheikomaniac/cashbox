<?php

namespace App\Controller;

use App\DTO\ContributionOutputDTO;
use App\Repository\ContributionPaymentRepository;
use App\Repository\ContributionRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/reports/contributions')]
class ContributionReportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContributionRepository $contributionRepository,
        private ContributionPaymentRepository $paymentRepository,
        private TeamRepository $teamRepository,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(Request $request): JsonResponse
    {
        $startDate = null;
        $endDate = null;
        $teamId = $request->query->get('teamId');

        if ($request->query->has('startDate')) {
            try {
                $startDate = new \DateTimeImmutable($request->query->get('startDate'));
            } catch (\Exception $e) {
                return $this->json(['message' => 'Invalid start date'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->query->has('endDate')) {
            try {
                $endDate = new \DateTimeImmutable($request->query->get('endDate'));
            } catch (\Exception $e) {
                return $this->json(['message' => 'Invalid end date'], Response::HTTP_BAD_REQUEST);
            }
        }

        $qb = $this->contributionRepository->createQueryBuilder('c')
            ->join('c.teamUser', 'tu')
            ->join('tu.team', 't')
            ->join('tu.user', 'u')
            ->join('c.type', 'ct');

        if ($teamId) {
            $team = $this->teamRepository->find($teamId);
            if (!$team) {
                return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
            }
            $qb->andWhere('t.id = :teamId')
               ->setParameter('teamId', $team->getId());
        }

        if ($startDate) {
            $qb->andWhere('c.dueDate >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('c.dueDate <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        $contributions = $qb->getQuery()->getResult();

        $contributionDTOs = array_map(
            fn ($contribution) => ContributionOutputDTO::createFromEntity($contribution),
            $contributions
        );

        return $this->json($contributionDTOs);
    }

    #[Route('/summary', methods: ['GET'])]
    public function getSummary(Request $request): JsonResponse
    {
        $teamId = $request->query->get('teamId');

        $qb = $this->contributionRepository->createQueryBuilder('c')
            ->join('c.teamUser', 'tu')
            ->join('tu.team', 't');

        if ($teamId) {
            $team = $this->teamRepository->find($teamId);
            if (!$team) {
                return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
            }
            $qb->andWhere('t.id = :teamId')
               ->setParameter('teamId', $team->getId());
        }

        $totalCount = $qb->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalAmount = $qb->select('SUM(c.amount)')
            ->getQuery()
            ->getSingleScalarResult();

        $paidCount = $qb->select('COUNT(c.id)')
            ->andWhere('c.paidAt IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $paidAmount = $qb->select('SUM(c.amount)')
            ->andWhere('c.paidAt IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $unpaidCount = $qb->select('COUNT(c.id)')
            ->andWhere('c.paidAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $unpaidAmount = $qb->select('SUM(c.amount)')
            ->andWhere('c.paidAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $overdueCount = $qb->select('COUNT(c.id)')
            ->andWhere('c.paidAt IS NULL')
            ->andWhere('c.dueDate < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();

        $overdueAmount = $qb->select('SUM(c.amount)')
            ->andWhere('c.paidAt IS NULL')
            ->andWhere('c.dueDate < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'total' => [
                'count' => (int) $totalCount,
                'amount' => (int) $totalAmount ?: 0,
            ],
            'paid' => [
                'count' => (int) $paidCount,
                'amount' => (int) $paidAmount ?: 0,
            ],
            'unpaid' => [
                'count' => (int) $unpaidCount,
                'amount' => (int) $unpaidAmount ?: 0,
            ],
            'overdue' => [
                'count' => (int) $overdueCount,
                'amount' => (int) $overdueAmount ?: 0,
            ],
        ]);
    }

    #[Route('/due', methods: ['GET'])]
    public function getDue(Request $request): JsonResponse
    {
        $teamId = $request->query->get('teamId');
        $daysAhead = $request->query->getInt('daysAhead', 30);

        $now = new \DateTimeImmutable();
        $futureDate = $now->modify("+{$daysAhead} days");

        $qb = $this->contributionRepository->createQueryBuilder('c')
            ->join('c.teamUser', 'tu')
            ->join('tu.team', 't')
            ->join('tu.user', 'u')
            ->join('c.type', 'ct')
            ->andWhere('c.paidAt IS NULL')
            ->andWhere('c.dueDate >= :now')
            ->andWhere('c.dueDate <= :futureDate')
            ->setParameter('now', $now)
            ->setParameter('futureDate', $futureDate);

        if ($teamId) {
            $team = $this->teamRepository->find($teamId);
            if (!$team) {
                return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
            }
            $qb->andWhere('t.id = :teamId')
               ->setParameter('teamId', $team->getId());
        }

        $contributions = $qb->getQuery()->getResult();

        $contributionDTOs = array_map(
            fn ($contribution) => ContributionOutputDTO::createFromEntity($contribution),
            $contributions
        );

        return $this->json($contributionDTOs);
    }

    #[Route('/paid', methods: ['GET'])]
    public function getPaid(Request $request): JsonResponse
    {
        $teamId = $request->query->get('teamId');
        $startDate = null;
        $endDate = null;

        if ($request->query->has('startDate')) {
            try {
                $startDate = new \DateTimeImmutable($request->query->get('startDate'));
            } catch (\Exception $e) {
                return $this->json(['message' => 'Invalid start date'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->query->has('endDate')) {
            try {
                $endDate = new \DateTimeImmutable($request->query->get('endDate'));
            } catch (\Exception $e) {
                return $this->json(['message' => 'Invalid end date'], Response::HTTP_BAD_REQUEST);
            }
        }

        $qb = $this->contributionRepository->createQueryBuilder('c')
            ->join('c.teamUser', 'tu')
            ->join('tu.team', 't')
            ->join('tu.user', 'u')
            ->join('c.type', 'ct')
            ->andWhere('c.paidAt IS NOT NULL');

        if ($teamId) {
            $team = $this->teamRepository->find($teamId);
            if (!$team) {
                return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
            }
            $qb->andWhere('t.id = :teamId')
               ->setParameter('teamId', $team->getId());
        }

        if ($startDate) {
            $qb->andWhere('c.paidAt >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('c.paidAt <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        $contributions = $qb->getQuery()->getResult();

        $contributionDTOs = array_map(
            fn ($contribution) => ContributionOutputDTO::createFromEntity($contribution),
            $contributions
        );

        return $this->json($contributionDTOs);
    }
}
