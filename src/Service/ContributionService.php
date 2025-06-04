<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contribution;
use App\Entity\ContributionType;
use App\Entity\TeamUser;
use App\Repository\ContributionRepository;
use App\ValueObject\Money;
use Doctrine\ORM\EntityManagerInterface;

readonly class ContributionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContributionRepository $contributionRepository,
    ) {}

    public function createContribution(
        TeamUser $teamUser,
        ContributionType $type,
        string $description,
        Money $amount,
        \DateTimeImmutable $dueDate
    ): Contribution {
        $contribution = new Contribution(
            teamUser: $teamUser,
            type: $type,
            description: $description,
            amount: $amount,
            dueDate: $dueDate
        );

        $this->entityManager->persist($contribution);
        $this->entityManager->flush();

        return $contribution;
    }

    public function markAsPaid(Contribution $contribution): void
    {
        $contribution->pay();
        $this->entityManager->flush();
    }

    public function getOutstandingContributions(TeamUser $teamUser): array
    {
        return $this->contributionRepository->findBy([
            'teamUser' => $teamUser,
            'paidAt' => null,
            'active' => true,
        ]);
    }

    public function getOverdueContributions(): array
    {
        return $this->contributionRepository->createQueryBuilder('c')
            ->where('c.paidAt IS NULL')
            ->andWhere('c.dueDate < :now')
            ->andWhere('c.active = true')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    public function calculateTotalOutstanding(TeamUser $teamUser): Money
    {
        $contributions = $this->getOutstandingContributions($teamUser);
        
        if (empty($contributions)) {
            return new Money(0, $this->getDefaultCurrency());
        }

        $total = $contributions[0]->getAmount();
        for ($i = 1; $i < count($contributions); $i++) {
            $total = $total->add($contributions[$i]->getAmount());
        }

        return $total;
    }

    private function getDefaultCurrency(): \App\Enum\CurrencyEnum
    {
        return \App\Enum\CurrencyEnum::EUR;
    }
}