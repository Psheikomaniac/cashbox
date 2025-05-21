<?php

namespace App\Controller;

use App\DTO\PenaltyDTO;
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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/penalties')]
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

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $penalties = $this->penaltyRepository->findAll();
        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    #[Route('/unpaid', methods: ['GET'])]
    public function getUnpaid(): JsonResponse
    {
        $penalties = $this->penaltyRepository->findUnpaid();
        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    #[Route('/team/{teamId}', methods: ['GET'])]
    public function getByTeam(string $teamId): JsonResponse
    {
        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $penalties = $this->penaltyRepository->findByTeam($team);
        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    #[Route('/user/{userId}', methods: ['GET'])]
    public function getByUser(string $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $penalties = $this->penaltyRepository->findByUser($user);
        $penaltyDTOs = array_map(fn (Penalty $penalty) => PenaltyDTO::createFromEntity($penalty), $penalties);

        return $this->json($penaltyDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            return $this->json(['message' => 'Penalty not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(PenaltyDTO::createFromEntity($penalty));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $teamUser = $this->findTeamUser($data['teamId'], $data['userId']);
        if (!$teamUser) {
            return $this->json(['message' => 'Team user not found'], Response::HTTP_BAD_REQUEST);
        }

        $penaltyType = $this->penaltyTypeRepository->find($data['typeId']);
        if (!$penaltyType) {
            return $this->json(['message' => 'Penalty type not found'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $currency = isset($data['currency']) ? CurrencyEnum::from($data['currency']) : CurrencyEnum::EUR;
        } catch (\ValueError $e) {
            return $this->json(['message' => 'Invalid currency'], Response::HTTP_BAD_REQUEST);
        }

        $penalty = new Penalty();
        $penalty->setTeamUser($teamUser);
        $penalty->setType($penaltyType);
        $penalty->setReason($data['reason']);
        $penalty->setAmount($data['amount']);
        $penalty->setCurrency($currency);
        $penalty->setArchived($data['archived'] ?? false);

        if (isset($data['paidAt']) && $data['paidAt']) {
            try {
                $paidAt = new \DateTimeImmutable($data['paidAt']);
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

        return $this->json(PenaltyDTO::createFromEntity($penalty), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            return $this->json(['message' => 'Penalty not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['teamId']) && isset($data['userId'])) {
            $teamUser = $this->findTeamUser($data['teamId'], $data['userId']);
            if (!$teamUser) {
                return $this->json(['message' => 'Team user not found'], Response::HTTP_BAD_REQUEST);
            }
            $penalty->setTeamUser($teamUser);
        }

        if (isset($data['typeId'])) {
            $penaltyType = $this->penaltyTypeRepository->find($data['typeId']);
            if (!$penaltyType) {
                return $this->json(['message' => 'Penalty type not found'], Response::HTTP_BAD_REQUEST);
            }
            $penalty->setType($penaltyType);
        }

        if (isset($data['reason'])) {
            $penalty->setReason($data['reason']);
        }

        if (isset($data['amount'])) {
            $penalty->setAmount($data['amount']);
        }

        if (isset($data['currency'])) {
            try {
                $currency = CurrencyEnum::from($data['currency']);
                $penalty->setCurrency($currency);
            } catch (\ValueError $e) {
                return $this->json(['message' => 'Invalid currency'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['archived'])) {
            $penalty->setArchived($data['archived']);
        }

        if (isset($data['paidAt'])) {
            if ($data['paidAt']) {
                try {
                    $paidAt = new \DateTimeImmutable($data['paidAt']);
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

        return $this->json(PenaltyDTO::createFromEntity($penalty));
    }

    #[Route('/{id}/pay', methods: ['POST'])]
    public function pay(string $id): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            return $this->json(['message' => 'Penalty not found'], Response::HTTP_NOT_FOUND);
        }

        $penalty->setPaidAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json(PenaltyDTO::createFromEntity($penalty));
    }

    #[Route('/{id}/archive', methods: ['POST'])]
    public function archive(string $id): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            return $this->json(['message' => 'Penalty not found'], Response::HTTP_NOT_FOUND);
        }

        $penalty->setArchived(true);

        $this->entityManager->flush();

        return $this->json(PenaltyDTO::createFromEntity($penalty));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            return $this->json(['message' => 'Penalty not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($penalty);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
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
