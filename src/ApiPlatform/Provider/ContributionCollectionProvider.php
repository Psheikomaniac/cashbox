<?php

namespace App\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\Contribution\ContributionResponseDTO;
use App\Repository\ContributionRepository;

class ContributionCollectionProvider implements ProviderInterface
{
    public function __construct(private readonly ContributionRepository $contributionRepository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Check for filters in the context
        $filters = $context['filters'] ?? [];

        // Determine which repository method to call based on filters
        if (isset($filters['unpaid']) && $filters['unpaid'] === 'true') {
            $contributions = $this->contributionRepository->findUnpaid();
        } elseif (isset($filters['upcoming']) && $filters['upcoming'] === 'true') {
            $contributions = $this->contributionRepository->findUpcoming(new \DateTimeImmutable());
        } elseif (isset($filters['team'])) {
            $contributions = $this->contributionRepository->findByTeamId($filters['team']);
        } elseif (isset($filters['user'])) {
            $contributions = $this->contributionRepository->findByUserId($filters['user']);
        } else {
            $contributions = $this->contributionRepository->findAll();
        }

        return array_map(
            fn ($contribution) => new ContributionResponseDTO(
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
            ),
            $contributions
        );
    }
}
