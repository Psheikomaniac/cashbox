<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ContributionType;
use App\Entity\TeamUser;
use App\Repository\ContributionRepository;
use App\Repository\ContributionTypeRepository;
use App\Repository\TeamUserRepository;
use App\ValueObject\Money;
use Doctrine\ORM\EntityManagerInterface;

readonly class RecurringContributionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContributionRepository $contributionRepository,
        private ContributionTypeRepository $contributionTypeRepository,
        private TeamUserRepository $teamUserRepository,
        private ContributionService $contributionService,
    ) {}

    public function processRecurringContributions(): int
    {
        $recurringTypes = $this->contributionTypeRepository->findBy([
            'recurring' => true,
            'active' => true,
        ]);

        $processedCount = 0;

        foreach ($recurringTypes as $type) {
            $processedCount += $this->processRecurringType($type);
        }

        return $processedCount;
    }

    public function processMonthlyContributions(): int
    {
        $monthlyTypes = $this->contributionTypeRepository->createQueryBuilder('ct')
            ->where('ct.recurring = true')
            ->andWhere('ct.recurrencePattern = :monthly')
            ->andWhere('ct.active = true')
            ->setParameter('monthly', \App\Enum\RecurrencePatternEnum::MONTHLY)
            ->getQuery()
            ->getResult();

        $processedCount = 0;

        foreach ($monthlyTypes as $type) {
            $processedCount += $this->processRecurringType($type);
        }

        return $processedCount;
    }

    private function processRecurringType(ContributionType $type): int
    {
        if (!$type->isRecurring() || !$type->getRecurrencePattern()) {
            return 0;
        }

        $teamUsers = $this->teamUserRepository->findAll();
        $processedCount = 0;
        $now = new \DateTimeImmutable();

        foreach ($teamUsers as $teamUser) {
            $lastContribution = $this->getLastContributionForType($teamUser, $type);
            
            if ($this->shouldCreateNextContribution($lastContribution, $type, $now)) {
                $nextDueDate = $type->calculateNextDueDate($lastContribution?->getDueDate() ?? $now);
                
                if ($nextDueDate) {
                    $this->createNextRecurringContribution($teamUser, $type, $nextDueDate);
                    $processedCount++;
                }
            }
        }

        return $processedCount;
    }

    private function getLastContributionForType(TeamUser $teamUser, ContributionType $type): ?\App\Entity\Contribution
    {
        return $this->contributionRepository->createQueryBuilder('c')
            ->where('c.teamUser = :teamUser')
            ->andWhere('c.type = :type')
            ->orderBy('c.dueDate', 'DESC')
            ->setMaxResults(1)
            ->setParameter('teamUser', $teamUser)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function shouldCreateNextContribution(
        ?\App\Entity\Contribution $lastContribution,
        ContributionType $type,
        \DateTimeImmutable $now
    ): bool {
        if (!$lastContribution) {
            return true; // First contribution for this type
        }

        $nextDueDate = $type->calculateNextDueDate($lastContribution->getDueDate());
        
        return $nextDueDate && $nextDueDate <= $now;
    }

    private function createNextRecurringContribution(
        TeamUser $teamUser,
        ContributionType $type,
        \DateTimeImmutable $dueDate
    ): void {
        // Get default amount - in a real implementation, this might come from a template or configuration
        $defaultAmount = new Money(5000, \App\Enum\CurrencyEnum::EUR); // €50.00

        $this->contributionService->createContribution(
            teamUser: $teamUser,
            type: $type,
            description: sprintf('Recurring %s for %s', $type->getName(), $dueDate->format('F Y')),
            amount: $defaultAmount,
            dueDate: $dueDate
        );
    }
}