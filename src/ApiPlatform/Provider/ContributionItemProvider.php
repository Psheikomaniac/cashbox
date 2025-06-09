<?php

namespace App\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\Contribution\ContributionResponseDTO;
use App\Repository\ContributionRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContributionItemProvider implements ProviderInterface
{
    public function __construct(private readonly ContributionRepository $contributionRepository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $id = $uriVariables['id'] ?? null;

        if (!$id) {
            throw new NotFoundHttpException('Contribution ID is required');
        }

        $contribution = $this->contributionRepository->find($id);

        if (!$contribution) {
            throw new NotFoundHttpException('Contribution not found');
        }

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
