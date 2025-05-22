<?php

namespace App\Controller;

use App\DTO\ContributionTemplateOutputDTO;
use App\Entity\Contribution;
use App\Entity\ContributionTemplate;
use App\Repository\ContributionRepository;
use App\Repository\ContributionTemplateRepository;
use App\Repository\ContributionTypeRepository;
use App\Repository\TeamRepository;
use App\Repository\TeamUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
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
        private ContributionRepository $contributionRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $templates = $this->templateRepository->findAll();
        $templateDTOs = array_map(
            fn (ContributionTemplate $template) => ContributionTemplateOutputDTO::createFromEntity($template),
            $templates
        );

        return $this->json($templateDTOs);
    }

    #[Route('/active', methods: ['GET'])]
    public function getActive(): JsonResponse
    {
        $templates = $this->templateRepository->findActive();
        $templateDTOs = array_map(
            fn (ContributionTemplate $template) => ContributionTemplateOutputDTO::createFromEntity($template),
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
            fn (ContributionTemplate $template) => ContributionTemplateOutputDTO::createFromEntity($template),
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

        return $this->json(ContributionTemplateOutputDTO::createFromEntity($template));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $team = $this->teamRepository->find($data['teamId']);
        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_BAD_REQUEST);
        }

        $template = new ContributionTemplate();
        $template->setTeam($team);
        $template->setName($data['name']);

        if (isset($data['description'])) {
            $template->setDescription($data['description']);
        }

        $template->setAmount($data['amount']);
        $template->setCurrency($data['currency'] ?? 'EUR');
        $template->setRecurring($data['recurring'] ?? false);

        if (isset($data['recurrencePattern'])) {
            $template->setRecurrencePattern($data['recurrencePattern']);
        }

        if (isset($data['dueDays'])) {
            $template->setDueDays($data['dueDays']);
        }

        $errors = $this->validator->validate($template);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($template);
        $this->entityManager->flush();

        return $this->json(ContributionTemplateOutputDTO::createFromEntity($template), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $template = $this->templateRepository->find($id);

        if (!$template) {
            return $this->json(['message' => 'Contribution template not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['teamId'])) {
            $team = $this->teamRepository->find($data['teamId']);
            if (!$team) {
                return $this->json(['message' => 'Team not found'], Response::HTTP_BAD_REQUEST);
            }
            $template->setTeam($team);
        }

        if (isset($data['name'])) {
            $template->setName($data['name']);
        }

        if (array_key_exists('description', $data)) {
            $template->setDescription($data['description']);
        }

        if (isset($data['amount'])) {
            $template->setAmount($data['amount']);
        }

        if (isset($data['currency'])) {
            $template->setCurrency($data['currency']);
        }

        if (isset($data['recurring'])) {
            $template->setRecurring($data['recurring']);
        }

        if (array_key_exists('recurrencePattern', $data)) {
            $template->setRecurrencePattern($data['recurrencePattern']);
        }

        if (array_key_exists('dueDays', $data)) {
            $template->setDueDays($data['dueDays']);
        }

        if (isset($data['active'])) {
            $template->setActive($data['active']);
        }

        $errors = $this->validator->validate($template);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(ContributionTemplateOutputDTO::createFromEntity($template));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $template = $this->templateRepository->find($id);

        if (!$template) {
            return $this->json(['message' => 'Contribution template not found'], Response::HTTP_NOT_FOUND);
        }

        $template->setActive(false);
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

            $contribution = new Contribution();
            $contribution->setTeamUser($teamUser);
            $contribution->setType($contributionType);
            $contribution->setDescription($data['description'] ?? $template->getName());
            $contribution->setAmount($data['amount'] ?? $template->getAmount());
            $contribution->setCurrency($data['currency'] ?? $template->getCurrency());
            $contribution->setDueDate($dueDate);

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
            'template' => ContributionTemplateOutputDTO::createFromEntity($template),
            'contributions' => $contributionDTOs,
            'count' => count($createdContributions),
        ]);
    }
}
