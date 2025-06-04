<?php

namespace App\Controller;

use App\DTO\ContributionOutputDTO;
use App\Entity\Contribution;
use App\Enum\CurrencyEnum;
use App\Repository\ContributionRepository;
use App\Repository\ContributionTypeRepository;
use App\Repository\TeamRepository;
use App\Repository\TeamUserRepository;
use App\Repository\UserRepository;
use App\ValueObject\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $contributions = $this->contributionRepository->findAll();
        $contributionDTOs = array_map(
            fn (Contribution $contribution) => ContributionOutputDTO::fromEntity($contribution),
            $contributions
        );

        return $this->json($contributionDTOs);
    }

    #[Route('/unpaid', methods: ['GET'])]
    public function getUnpaid(): JsonResponse
    {
        $contributions = $this->contributionRepository->findUnpaid();
        $contributionDTOs = array_map(
            fn (Contribution $contribution) => ContributionOutputDTO::fromEntity($contribution),
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
            fn (Contribution $contribution) => ContributionOutputDTO::fromEntity($contribution),
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
            fn (Contribution $contribution) => ContributionOutputDTO::fromEntity($contribution),
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
            fn (Contribution $contribution) => ContributionOutputDTO::fromEntity($contribution),
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

        return $this->json(ContributionOutputDTO::fromEntity($contribution));
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

        $money = new Money($data['amount'], CurrencyEnum::tryFrom($data['currency'] ?? 'EUR') ?? CurrencyEnum::EUR);
        $contribution = new Contribution(
            teamUser: $teamUser,
            type: $contributionType,
            description: $data['description'],
            amount: $money,
            dueDate: new \DateTimeImmutable($data['dueDate'])
        );

        if (isset($data['paidAt']) && $data['paidAt']) {
            try {
                // If paidAt is provided and not null, mark as paid
                $contribution->pay();
            } catch (\Exception $e) {
                return $this->json(['message' => 'Could not mark as paid: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }
        }

        $errors = $this->validator->validate($contribution);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($contribution);
        $this->entityManager->flush();

        return $this->json(ContributionOutputDTO::fromEntity($contribution), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $contribution = $this->contributionRepository->find($id);

        if (!$contribution) {
            return $this->json(['message' => 'Contribution not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Note: In rich domain model, core properties like teamUser, type cannot be changed
        // after creation. Only certain business operations are allowed.
        
        if (isset($data['description'])) {
            // Description updates would need a dedicated business method in a real implementation
            // For now, we'll skip this as the entity doesn't provide setDescription
        }

        // Note: Amount and currency cannot be updated in the rich domain model
        // They are set at creation time through the Money value object
        // To change the amount, a new contribution should be created

        if (isset($data['dueDate'])) {
            try {
                $dueDate = new \DateTimeImmutable($data['dueDate']);
                $contribution->updateDueDate($dueDate);
            } catch (\Exception $e) {
                return $this->json(['message' => 'Invalid due date'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['paidAt'])) {
            if ($data['paidAt']) {
                try {
                    // Mark contribution as paid
                    $contribution->pay();
                } catch (\Exception $e) {
                    return $this->json(['message' => 'Could not mark as paid: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
                }
            }
            // Note: Cannot "unpay" a contribution in the domain model for audit reasons
        }

        if (isset($data['active'])) {
            if ($data['active']) {
                $contribution->activate();
            } else {
                $contribution->deactivate();
            }
        }

        $errors = $this->validator->validate($contribution);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(ContributionOutputDTO::fromEntity($contribution));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $contribution = $this->contributionRepository->find($id);

        if (!$contribution) {
            return $this->json(['message' => 'Contribution not found'], Response::HTTP_NOT_FOUND);
        }

        $contribution->deactivate();
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

        $contribution->pay();
        $this->entityManager->flush();

        return $this->json(ContributionOutputDTO::fromEntity($contribution));
    }

}
