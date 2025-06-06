<?php

namespace App\Controller;

use App\DTO\PenaltyInputDTO;
use App\DTO\PenaltyOutputDTO;
use App\Entity\Penalty;
use App\Enum\CurrencyEnum;
use App\Repository\PenaltyRepository;
use App\Repository\PenaltyTypeRepository;
use App\Repository\TeamRepository;
use App\Repository\TeamUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/penalties', name: 'api_penalties_')]
class PenaltyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PenaltyRepository $penaltyRepository,
        private PenaltyTypeRepository $penaltyTypeRepository,
        private TeamRepository $teamRepository,
        private UserRepository $userRepository,
        private TeamUserRepository $teamUserRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getAll(): JsonResponse
    {
        $penalties = $this->penaltyRepository->findAll();
        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyOutputDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    #[Route('/unpaid', name: 'unpaid', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUnpaid(): JsonResponse
    {
        $penalties = $this->penaltyRepository->findUnpaid();
        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyOutputDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    #[Route('/team/{teamId}', name: 'by_team', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getByTeam(string $teamId): JsonResponse
    {
        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $penalties = $this->penaltyRepository->findByTeam($team);
        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyOutputDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    #[Route('/user/{userId}', name: 'by_user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getByUser(string $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $penalties = $this->penaltyRepository->findByUser($user);
        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyOutputDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getOne(string $id): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            return $this->json(['message' => 'Penalty not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(PenaltyOutputDTO::createFromEntity($penalty));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Create and populate PenaltyInputDTO
        $penaltyInputDTO = new PenaltyInputDTO();
        $penaltyInputDTO->teamId = $data['teamId'] ?? '';
        $penaltyInputDTO->userId = $data['userId'] ?? '';
        $penaltyInputDTO->typeId = $data['typeId'] ?? '';
        $penaltyInputDTO->reason = $data['reason'] ?? '';
        $penaltyInputDTO->amount = $data['amount'] ?? 0;
        $penaltyInputDTO->currency = $data['currency'] ?? 'EUR';
        $penaltyInputDTO->archived = $data['archived'] ?? false;
        $penaltyInputDTO->paidAt = $data['paidAt'] ?? null;

        // Validate the DTO (optional, can be added later)

        $teamUser = $this->findTeamUser($penaltyInputDTO->teamId, $penaltyInputDTO->userId);
        if (!$teamUser) {
            return $this->json(['message' => 'Team user not found'], Response::HTTP_BAD_REQUEST);
        }

        $penaltyType = $this->penaltyTypeRepository->find($penaltyInputDTO->typeId);
        if (!$penaltyType) {
            return $this->json(['message' => 'Penalty type not found'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $currency = CurrencyEnum::from($penaltyInputDTO->currency);
        } catch (\ValueError $e) {
            return $this->json(['message' => 'Invalid currency'], Response::HTTP_BAD_REQUEST);
        }

        $penalty = new Penalty();
        $penalty->setTeamUser($teamUser);
        $penalty->setType($penaltyType);
        $penalty->setReason($penaltyInputDTO->reason);
        $penalty->setAmount($penaltyInputDTO->amount);
        $penalty->setCurrency($currency);
        $penalty->setArchived($penaltyInputDTO->archived);

        if ($penaltyInputDTO->paidAt) {
            try {
                $paidAt = new \DateTimeImmutable($penaltyInputDTO->paidAt);
                $penalty->setPaidAt($paidAt);
            } catch (\Exception $e) {
                return $this->json(['message' => 'Invalid paid at date'], Response::HTTP_BAD_REQUEST);
            }
        }

        $errors = $this->validator->validate($penalty);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($penalty);
        $this->entityManager->flush();

        return $this->json(PenaltyOutputDTO::createFromEntity($penalty), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(string $id, Request $request): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            return $this->json(['message' => 'Penalty not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Create and populate PenaltyInputDTO with only the fields that are present in the request
        $penaltyInputDTO = new PenaltyInputDTO();

        if (isset($data['teamId']) && isset($data['userId'])) {
            $penaltyInputDTO->teamId = $data['teamId'];
            $penaltyInputDTO->userId = $data['userId'];
            $teamUser = $this->findTeamUser($penaltyInputDTO->teamId, $penaltyInputDTO->userId);
            if (!$teamUser) {
                return $this->json(['message' => 'Team user not found'], Response::HTTP_BAD_REQUEST);
            }
            $penalty->setTeamUser($teamUser);
        }

        if (isset($data['typeId'])) {
            $penaltyInputDTO->typeId = $data['typeId'];
            $penaltyType = $this->penaltyTypeRepository->find($penaltyInputDTO->typeId);
            if (!$penaltyType) {
                return $this->json(['message' => 'Penalty type not found'], Response::HTTP_BAD_REQUEST);
            }
            $penalty->setType($penaltyType);
        }

        if (isset($data['reason'])) {
            $penaltyInputDTO->reason = $data['reason'];
            $penalty->setReason($penaltyInputDTO->reason);
        }

        if (isset($data['amount'])) {
            $penaltyInputDTO->amount = $data['amount'];
            $penalty->setAmount($penaltyInputDTO->amount);
        }

        if (isset($data['currency'])) {
            $penaltyInputDTO->currency = $data['currency'];
            try {
                $currency = CurrencyEnum::from($penaltyInputDTO->currency);
                $penalty->setCurrency($currency);
            } catch (\ValueError $e) {
                return $this->json(['message' => 'Invalid currency'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['archived'])) {
            $penaltyInputDTO->archived = $data['archived'];
            $penalty->setArchived($penaltyInputDTO->archived);
        }

        if (isset($data['paidAt'])) {
            $penaltyInputDTO->paidAt = $data['paidAt'];
            if ($penaltyInputDTO->paidAt) {
                try {
                    $paidAt = new \DateTimeImmutable($penaltyInputDTO->paidAt);
                    $penalty->setPaidAt($paidAt);
                } catch (\Exception $e) {
                    return $this->json(['message' => 'Invalid paid at date'], Response::HTTP_BAD_REQUEST);
                }
            } else {
                $penalty->setPaidAt(null);
            }
        }

        $errors = $this->validator->validate($penalty);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(PenaltyOutputDTO::createFromEntity($penalty));
    }

    #[Route('/{id}/pay', name: 'pay', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function pay(string $id): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            return $this->json(['message' => 'Penalty not found'], Response::HTTP_NOT_FOUND);
        }

        $penalty->setPaidAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json(PenaltyOutputDTO::createFromEntity($penalty));
    }

    #[Route('/{id}/archive', name: 'archive', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function archive(string $id): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            return $this->json(['message' => 'Penalty not found'], Response::HTTP_NOT_FOUND);
        }

        $penalty->setArchived(true);

        $this->entityManager->flush();

        return $this->json(PenaltyOutputDTO::createFromEntity($penalty));
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q');
        $typeId = $request->query->get('typeId');
        $teamId = $request->query->get('teamId');
        $userId = $request->query->get('userId');
        $paid = $request->query->get('paid');
        $archived = $request->query->get('archived');
        $minAmount = $request->query->get('minAmount');
        $maxAmount = $request->query->get('maxAmount');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $criteria = [];

        if ($typeId) {
            $penaltyType = $this->penaltyTypeRepository->find($typeId);
            if ($penaltyType) {
                $criteria['type'] = $penaltyType;
            }
        }

        if ($teamId) {
            $team = $this->teamRepository->find($teamId);
            if ($team) {
                $criteria['team'] = $team;
            }
        }

        if ($userId) {
            $user = $this->userRepository->find($userId);
            if ($user) {
                $criteria['user'] = $user;
            }
        }

        if ($paid !== null) {
            $criteria['paid'] = filter_var($paid, FILTER_VALIDATE_BOOLEAN);
        }

        if ($archived !== null) {
            $criteria['archived'] = filter_var($archived, FILTER_VALIDATE_BOOLEAN);
        }

        // Advanced search with query, amount range, and date range would be implemented in the repository
        $penalties = $this->penaltyRepository->findByAdvancedCriteria(
            $criteria,
            $query,
            $minAmount,
            $maxAmount,
            $startDate ? new \DateTimeImmutable($startDate) : null,
            $endDate ? new \DateTimeImmutable($endDate) : null
        );

        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyOutputDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    #[Route('/statistics', name: 'statistics', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function getStatistics(Request $request): JsonResponse
    {
        $teamId = $request->query->get('teamId');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $startDateTime = $startDate ? new \DateTimeImmutable($startDate) : null;
        $endDateTime = $endDate ? new \DateTimeImmutable($endDate) : null;

        $team = $teamId ? $this->teamRepository->find($teamId) : null;

        $statistics = $this->penaltyRepository->getStatistics($team, $startDateTime, $endDateTime);

        return $this->json($statistics);
    }

    #[Route('/by-date-range', name: 'by_date_range', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getByDateRange(Request $request): JsonResponse
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        if (!$startDate || !$endDate) {
            return $this->json(['message' => 'Start date and end date are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $startDateTime = new \DateTimeImmutable($startDate);
            $endDateTime = new \DateTimeImmutable($endDate);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        $penalties = $this->penaltyRepository->findByDateRange($startDateTime, $endDateTime);
        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyOutputDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    #[Route('/by-type', name: 'by_type', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getByType(Request $request): JsonResponse
    {
        $typeId = $request->query->get('typeId');

        if (!$typeId) {
            return $this->json(['message' => 'Type ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $penaltyType = $this->penaltyTypeRepository->find($typeId);

        if (!$penaltyType) {
            return $this->json(['message' => 'Penalty type not found'], Response::HTTP_NOT_FOUND);
        }

        $penalties = $this->penaltyRepository->findByType($penaltyType);
        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyOutputDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    private function findTeamUser(string $teamId, string $userId)
    {
        $team = $this->teamRepository->find($teamId);
        $user = $this->userRepository->find($userId);

        if (!$team || !$user) {
            return null;
        }

        return $this->teamUserRepository->findOneByTeamAndUser($team, $user);
    }
}
