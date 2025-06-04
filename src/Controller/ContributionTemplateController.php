<?php

namespace App\Controller;

use App\DTO\ContributionTemplateOutputDTO;
use App\Entity\Contribution;
use App\Entity\ContributionTemplate;
use App\Entity\ContributionType;
use App\Enum\CurrencyEnum;
use App\Enum\RecurrencePatternEnum;
use App\Repository\ContributionTemplateRepository;
use App\Repository\ContributionTypeRepository;
use App\Repository\TeamRepository;
use App\Repository\TeamUserRepository;
use App\ValueObject\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/contribution-templates')]
class ContributionTemplateController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContributionTemplateRepository $templateRepository,
        private TeamRepository $teamRepository,
        private TeamUserRepository $teamUserRepository,
        private ContributionTypeRepository $typeRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $templates = $this->templateRepository->findAll();
        $templateDTOs = array_map(
            fn (ContributionTemplate $template) => ContributionTemplateOutputDTO::fromEntity($template),
            $templates
        );

        return $this->json($templateDTOs);
    }

    #[Route('/active', methods: ['GET'])]
    public function getActive(): JsonResponse
    {
        $templates = $this->templateRepository->findActive();
        $templateDTOs = array_map(
            fn (ContributionTemplate $template) => ContributionTemplateOutputDTO::fromEntity($template),
            $templates
        );

        return $this->json($templateDTOs);
    }

    #[Route('/team/{teamId}', methods: ['GET'])]
    public function getByTeam(string $teamId): JsonResponse
    {
        $team = $this->teamRepository->find($teamId);
        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $templates = $this->templateRepository->findByTeam($team);
        $templateDTOs = array_map(
            fn (ContributionTemplate $template) => ContributionTemplateOutputDTO::fromEntity($template),
            $templates
        );

        return $this->json($templateDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $template = $this->templateRepository->find($id);

        if (!$template) {
            return $this->json(['message' => 'Contribution template not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(ContributionTemplateOutputDTO::fromEntity($template));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $team = $this->teamRepository->find($data['teamId']);
        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_BAD_REQUEST);
        }

        $currency = CurrencyEnum::tryFrom($data['currency'] ?? 'EUR') ?? CurrencyEnum::EUR;
        $money = new Money($data['amount'], $currency);
        $recurrencePattern = isset($data['recurrencePattern']) ? RecurrencePatternEnum::tryFrom($data['recurrencePattern']) : null;
        
        $template = new ContributionTemplate(
            team: $team,
            name: $data['name'],
            amount: $money,
            description: $data['description'] ?? null,
            recurring: $data['recurring'] ?? false,
            recurrencePattern: $recurrencePattern,
            dueDays: $data['dueDays'] ?? null
        );

        $errors = $this->validator->validate($template);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($template);
        $this->entityManager->flush();

        return $this->json(ContributionTemplateOutputDTO::fromEntity($template), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $template = $this->templateRepository->find($id);

        if (!$template) {
            return $this->json(['message' => 'Contribution template not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Note: In rich domain model, team cannot be changed after creation
        // Only business properties can be updated through the update method
        
        if (isset($data['name']) || isset($data['amount']) || isset($data['currency'])) {
            $name = $data['name'] ?? $template->getName();
            $currency = isset($data['currency']) ? 
                (CurrencyEnum::tryFrom($data['currency']) ?? $template->getAmount()->getCurrency()) : 
                $template->getAmount()->getCurrency();
            $amount = isset($data['amount']) ? 
                new Money($data['amount'], $currency) : 
                $template->getAmount();
            
            $recurrencePattern = array_key_exists('recurrencePattern', $data) ? 
                (isset($data['recurrencePattern']) ? RecurrencePatternEnum::tryFrom($data['recurrencePattern']) : null) : 
                $template->getRecurrencePattern();
            
            $template->update(
                name: $name,
                amount: $amount,
                description: array_key_exists('description', $data) ? $data['description'] : $template->getDescription(),
                recurring: $data['recurring'] ?? $template->isRecurring(),
                recurrencePattern: $recurrencePattern,
                dueDays: array_key_exists('dueDays', $data) ? $data['dueDays'] : $template->getDueDays()
            );
        }

        // Note: Active status would need domain methods like activate()/deactivate()
        // For now we'll skip this field since setActive() doesn't exist in the entity

        $errors = $this->validator->validate($template);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(ContributionTemplateOutputDTO::fromEntity($template));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $template = $this->templateRepository->find($id);

        if (!$template) {
            return $this->json(['message' => 'Contribution template not found'], Response::HTTP_NOT_FOUND);
        }

        // Note: Rich domain model should have activate()/deactivate() methods
        // For now, we'll remove the template from the database
        $this->entityManager->remove($template);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/apply', methods: ['POST'])]
    public function apply(string $id, Request $request): JsonResponse
    {
        $template = $this->templateRepository->find($id);

        if (!$template) {
            return $this->json(['message' => 'Contribution template not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['teamUserIds']) || !is_array($data['teamUserIds']) || empty($data['teamUserIds'])) {
            return $this->json(['message' => 'Team user IDs are required'], Response::HTTP_BAD_REQUEST);
        }

        $contributionType = null;
        if (isset($data['typeId'])) {
            $contributionType = $this->typeRepository->find($data['typeId']);
            if (!$contributionType) {
                return $this->json(['message' => 'Contribution type not found'], Response::HTTP_BAD_REQUEST);
            }
        }
        
        // If no specific type provided, use the template to determine the type
        if (!$contributionType) {
            // Create a basic contribution type based on template
            $contributionType = new ContributionType(
                name: $template->getName(),
                description: $template->getDescription(),
                recurring: $template->isRecurring(),
                recurrencePattern: $template->getRecurrencePattern()
            );
            $this->entityManager->persist($contributionType);
        }

        $dueDate = null;
        if (isset($data['dueDate'])) {
            try {
                $dueDate = new \DateTimeImmutable($data['dueDate']);
            } catch (\Exception $e) {
                return $this->json(['message' => 'Invalid due date'], Response::HTTP_BAD_REQUEST);
            }
        } else if ($template->getDueDays() !== null) {
            $dueDate = (new \DateTimeImmutable())->modify('+' . $template->getDueDays() . ' days');
        } else {
            return $this->json(['message' => 'Due date is required'], Response::HTTP_BAD_REQUEST);
        }

        $createdContributions = [];

        foreach ($data['teamUserIds'] as $teamUserId) {
            $teamUser = $this->teamUserRepository->find($teamUserId);
            if (!$teamUser) {
                continue;
            }

            $amount = isset($data['amount']) ? 
                new Money($data['amount'], $template->getAmount()->getCurrency()) : 
                $template->getAmount();
                
            $contribution = new Contribution(
                teamUser: $teamUser,
                type: $contributionType,
                description: $data['description'] ?? $template->getName(),
                amount: $amount,
                dueDate: $dueDate
            );

            $this->entityManager->persist($contribution);
            $createdContributions[] = $contribution;
        }

        $this->entityManager->flush();

        $contributionDTOs = array_map(
            fn (Contribution $contribution) => [
                'id' => $contribution->getId()->toString(),
                'teamUserId' => $contribution->getTeamUser()->getId()->toString(),
                'amount' => $contribution->getAmount(),
                'dueDate' => $contribution->getDueDate()->format('Y-m-d'),
            ],
            $createdContributions
        );

        return $this->json([
            'template' => ContributionTemplateOutputDTO::fromEntity($template),
            'contributions' => $contributionDTOs,
            'count' => count($createdContributions),
        ]);
    }
}
