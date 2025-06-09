<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ContributionTemplate;
use App\Entity\TeamUser;
use App\Repository\ContributionTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ContributionTemplateService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContributionTemplateRepository $templateRepository,
        private ContributionService $contributionService,
    ) {}

    /**
     * @param array<TeamUser> $teamUsers
     * @return array<\App\Entity\Contribution>
     */
    public function applyTemplateToUsers(
        ContributionTemplate $template,
        array $teamUsers
    ): array {
        $contributions = $template->applyToUsers($teamUsers);

        foreach ($contributions as $contribution) {
            $this->entityManager->persist($contribution);
        }

        $this->entityManager->flush();

        return $contributions;
    }

    /**
     * @param array<TeamUser> $teamUsers
     * @return array<\App\Entity\Contribution>
     */
    public function createBulkContributions(
        ContributionTemplate $template,
        array $teamUsers
    ): array {
        if (empty($teamUsers)) {
            return [];
        }

        return $this->applyTemplateToUsers($template, $teamUsers);
    }

    /**
     * @return array<ContributionTemplate>
     */
    public function getActiveTemplatesForTeam(string $teamId): array
    {
        return $this->templateRepository->createQueryBuilder('ct')
            ->where('ct.team = :teamId')
            ->andWhere('ct.active = true')
            ->setParameter('teamId', $teamId)
            ->orderBy('ct.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function duplicateTemplate(
        ContributionTemplate $originalTemplate,
        string $newName
    ): ContributionTemplate {
        $newTemplate = new ContributionTemplate(
            team: $originalTemplate->getTeam(),
            name: $newName,
            amount: $originalTemplate->getAmount(),
            description: $originalTemplate->getDescription(),
            recurring: $originalTemplate->isRecurring(),
            recurrencePattern: $originalTemplate->getRecurrencePattern(),
            dueDays: $originalTemplate->getDueDays()
        );

        $this->entityManager->persist($newTemplate);
        $this->entityManager->flush();

        return $newTemplate;
    }
}
