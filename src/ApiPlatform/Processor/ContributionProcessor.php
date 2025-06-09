<?php

namespace App\ApiPlatform\Processor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\Contribution\ContributionResponseDTO;
use App\DTO\Contribution\CreateContributionDTO;
use App\DTO\Contribution\UpdateContributionDTO;
use App\Entity\Contribution;
use App\Enum\CurrencyEnum;
use App\Repository\ContributionRepository;
use App\Repository\ContributionTypeRepository;
use App\Repository\TeamUserRepository;
use App\ValueObject\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContributionProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TeamUserRepository $teamUserRepository,
        private readonly ContributionTypeRepository $contributionTypeRepository,
        private readonly ContributionRepository $contributionRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object|null
    {
        // Handle creation
        if ($data instanceof CreateContributionDTO) {
            return $this->createContribution($data);
        }

        // Handle update
        if ($data instanceof UpdateContributionDTO && isset($uriVariables['id'])) {
            return $this->updateContribution($data, $uriVariables['id']);
        }

        // Handle pay operation
        if (isset($uriVariables['id']) && isset($context['operation_name']) && $context['operation_name'] === 'pay') {
            return $this->payContribution($uriVariables['id']);
        }

        // Handle delete operation (deactivate)
        if ($operation instanceof Delete && isset($uriVariables['id'])) {
            return $this->deleteContribution($uriVariables['id']);
        }

        throw new \InvalidArgumentException('Unsupported data type or operation');
    }

    private function createContribution(CreateContributionDTO $dto): ContributionResponseDTO
    {
        $teamUser = $this->teamUserRepository->find($dto->teamUserId);
        if (!$teamUser) {
            throw new NotFoundHttpException('Team user not found');
        }

        $contributionType = $this->contributionTypeRepository->find($dto->typeId);
        if (!$contributionType) {
            throw new NotFoundHttpException('Contribution type not found');
        }

        try {
            $currency = CurrencyEnum::tryFrom($dto->currency) ?? CurrencyEnum::EUR;
            $money = new Money($dto->amount, $currency);
            $dueDate = new \DateTimeImmutable($dto->dueDate);

            $contribution = new Contribution(
                teamUser: $teamUser,
                type: $contributionType,
                description: $dto->description,
                amount: $money,
                dueDate: $dueDate
            );

            if ($dto->paid) {
                $contribution->pay();
            }

            $errors = $this->validator->validate($contribution);
            if (count($errors) > 0) {
                throw new BadRequestHttpException((string) $errors);
            }

            $this->entityManager->persist($contribution);
            $this->entityManager->flush();

            return $this->createResponseDTO($contribution);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    private function updateContribution(UpdateContributionDTO $dto, string $id): ContributionResponseDTO
    {
        $contribution = $this->contributionRepository->find($id);
        if (!$contribution) {
            throw new NotFoundHttpException('Contribution not found');
        }

        try {
            // Update due date if provided
            if ($dto->dueDate !== null) {
                $dueDate = new \DateTimeImmutable($dto->dueDate);
                $contribution->updateDueDate($dueDate);
            }

            // Update paid status if provided
            if ($dto->paid !== null && $dto->paid && !$contribution->isPaid()) {
                $contribution->pay();
            }

            // Update active status if provided
            if ($dto->active !== null) {
                if ($dto->active) {
                    $contribution->activate();
                } else {
                    $contribution->deactivate();
                }
            }

            $errors = $this->validator->validate($contribution);
            if (count($errors) > 0) {
                throw new BadRequestHttpException((string) $errors);
            }

            $this->entityManager->flush();

            return $this->createResponseDTO($contribution);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    private function payContribution(string $id): ContributionResponseDTO
    {
        $contribution = $this->contributionRepository->find($id);
        if (!$contribution) {
            throw new NotFoundHttpException('Contribution not found');
        }

        try {
            $contribution->pay();
            $this->entityManager->flush();

            return $this->createResponseDTO($contribution);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    private function deleteContribution(string $id): ?object
    {
        $contribution = $this->contributionRepository->find($id);
        if (!$contribution) {
            throw new NotFoundHttpException('Contribution not found');
        }

        try {
            // Soft delete by deactivating
            $contribution->deactivate();
            $this->entityManager->flush();

            // Return null for DELETE operations
            return null;
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    private function createResponseDTO(Contribution $contribution): ContributionResponseDTO
    {
        return new ContributionResponseDTO(
            $contribution->getId()->toString(),
            $contribution->getTeamUser()->getId()->toString(),
            $contribution->getTeamUser()->getUser()->getName()->getFullName(),
            $contribution->getTeamUser()->getTeam()->getName(),
            $contribution->getType()->getId()->toString(),
            $contribution->getType()->getName(),
            $contribution->getDescription(),
            $contribution->getAmount()->getCents(),
            $contribution->getAmount()->getCurrency()->value,
            $contribution->getDueDate()->format('Y-m-d'),
            $contribution->getPaidAt()?->format('Y-m-d'),
            $contribution->isActive(),
            $contribution->isPaid(),
            $contribution->isOverdue(),
            $contribution->getCreatedAt(),
            $contribution->getUpdatedAt()
        );
    }
}
