<?php

namespace App\Controller;

use App\DTO\ContributionOutputDTO;
use App\Entity\Contribution;
use App\Repository\ContributionRepository;
use App\Repository\ContributionTypeRepository;
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

#[Route('/api/contributions')]
class ContributionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContributionRepository $contributionRepository,
        private ContributionTypeRepository $contributionTypeRepository,
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
        $contributions = $this->contributionRepository->findAll();
        $contributionDTOs = array_map(
            fn (Contribution $contribution) => ContributionOutputDTO::createFromEntity($contribution),
            $contributions
        );

        return $this->json($contributionDTOs);
    }

    #[Route('/unpaid', methods: ['GET'])]
    public function getUnpaid(): JsonResponse
    {
        $contributions = $this->contributionRepository->findUnpaid();
        $contributionDTOs = array_map(
            fn (Contribution $contribution) => ContributionOutputDTO::createFromEntity($contribution),
            $contributions
        );

        return $this->json($contributionDTOs);
    }

    #[Route('/upcoming', methods: ['GET'])]
    public function getUpcoming(): JsonResponse
    {
        $now = new \DateTimeImmutable();
        $contributions = $this->contributionRepository->findUpcoming($now);
        $contributionDTOs = array_map(
            fn (Contribution $contribution) => ContributionOutputDTO::createFromEntity($contribution),
            $contributions
        );

        return $this->json($contributionDTOs);
    }

    #[Route('/team/{teamId}', methods: ['GET'])]
    public function getByTeam(string $teamId): JsonResponse
    {
        $team = $this->teamRepository->find($teamId);
        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $contributions = $this->contributionRepository->findByTeam($team);
        $contributionDTOs = array_map(
            fn (Contribution $contribution) => ContributionOutputDTO::createFromEntity($contribution),
            $contributions
        );

        return $this->json($contributionDTOs);
    }

    #[Route('/user/{userId}', methods: ['GET'])]
    public function getByUser(string $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $contributions = $this->contributionRepository->findByUser($user);
        $contributionDTOs = array_map(
            fn (Contribution $contribution) => ContributionOutputDTO::createFromEntity($contribution),
            $contributions
        );

        return $this->json($contributionDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $contribution = $this->contributionRepository->find($id);

        if (!$contribution) {
            return $this->json(['message' => 'Contribution not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(ContributionOutputDTO::createFromEntity($contribution));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $teamUser = $this->teamUserRepository->find($data['teamUserId']);
        if (!$teamUser) {
            return $this->json(['message' => 'Team user not found'], Response::HTTP_BAD_REQUEST);
        }

        $contributionType = $this->contributionTypeRepository->find($data['typeId']);
        if (!$contributionType) {
            return $this->json(['message' => 'Contribution type not found'], Response::HTTP_BAD_REQUEST);
        }

        $contribution = new Contribution();
        $contribution->setTeamUser($teamUser);
        $contribution->setType($contributionType);
        $contribution->setDescription($data['description']);
        $contribution->setAmount($data['amount']);
        $contribution->setCurrency($data['currency'] ?? 'EUR');

        try {
            $dueDate = new \DateTimeImmutable($data['dueDate']);
            $contribution->setDueDate($dueDate);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Invalid due date'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['paidAt']) && $data['paidAt']) {
            try {
                $paidAt = new \DateTimeImmutable($data['paidAt']);
                $contribution->setPaidAt($paidAt);
            } catch (\Exception $e) {
                return $this->json(['message' => 'Invalid paid at date'], Response::HTTP_BAD_REQUEST);
            }
        }

        $errors = $this->validator->validate($contribution);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($contribution);
        $this->entityManager->flush();

        return $this->json(ContributionOutputDTO::createFromEntity($contribution), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $contribution = $this->contributionRepository->find($id);

        if (!$contribution) {
            return $this->json(['message' => 'Contribution not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['teamUserId'])) {
            $teamUser = $this->teamUserRepository->find($data['teamUserId']);
            if (!$teamUser) {
                return $this->json(['message' => 'Team user not found'], Response::HTTP_BAD_REQUEST);
            }
            $contribution->setTeamUser($teamUser);
        }

        if (isset($data['typeId'])) {
            $contributionType = $this->contributionTypeRepository->find($data['typeId']);
            if (!$contributionType) {
                return $this->json(['message' => 'Contribution type not found'], Response::HTTP_BAD_REQUEST);
            }
            $contribution->setType($contributionType);
        }

        if (isset($data['description'])) {
            $contribution->setDescription($data['description']);
        }

        if (isset($data['amount'])) {
            $contribution->setAmount($data['amount']);
        }

        if (isset($data['currency'])) {
            $contribution->setCurrency($data['currency']);
        }

        if (isset($data['dueDate'])) {
            try {
                $dueDate = new \DateTimeImmutable($data['dueDate']);
                $contribution->setDueDate($dueDate);
            } catch (\Exception $e) {
                return $this->json(['message' => 'Invalid due date'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['paidAt'])) {
            if ($data['paidAt']) {
                try {
                    $paidAt = new \DateTimeImmutable($data['paidAt']);
                    $contribution->setPaidAt($paidAt);
                } catch (\Exception $e) {
                    return $this->json(['message' => 'Invalid paid at date'], Response::HTTP_BAD_REQUEST);
                }
            } else {
                $contribution->setPaidAt(null);
            }
        }

        if (isset($data['active'])) {
            $contribution->setActive($data['active']);
        }

        $errors = $this->validator->validate($contribution);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(ContributionOutputDTO::createFromEntity($contribution));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $contribution = $this->contributionRepository->find($id);

        if (!$contribution) {
            return $this->json(['message' => 'Contribution not found'], Response::HTTP_NOT_FOUND);
        }

        $contribution->setActive(false);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/pay', methods: ['POST'])]
    public function pay(string $id): JsonResponse
    {
        $contribution = $this->contributionRepository->find($id);

        if (!$contribution) {
            return $this->json(['message' => 'Contribution not found'], Response::HTTP_NOT_FOUND);
        }

        $contribution->setPaidAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json(ContributionOutputDTO::createFromEntity($contribution));
    }

    private function findTeamUser(string $teamId, string $userId)
    {
        $team = $this->teamRepository->find($teamId);
        $user = $this->userRepository->find($userId);

        if (!$team || !$user) {
            return null;
        }

        return $this->teamUserRepository->findOneBy([
            'team' => $team,
            'user' => $user,
        ]);
    }
}
