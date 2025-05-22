<?php

namespace App\Controller;

use App\DTO\ReportInputDTO;
use App\DTO\ReportOutputDTO;
use App\Entity\Report;
use App\Message\ReportGenerationMessage;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/reports')]
class ReportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ReportRepository $reportRepository,
        private UserRepository $userRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private MessageBusInterface $messageBus
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $reports = $this->reportRepository->findAll();
        $reportDTOs = array_map(fn (Report $report) => ReportOutputDTO::createFromEntity($report), $reports);

        return $this->json($reportDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $report = $this->reportRepository->find($id);

        if (!$report) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(ReportOutputDTO::createFromEntity($report));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Create and populate ReportInputDTO
        $reportInputDTO = new ReportInputDTO();
        $reportInputDTO->name = $data['name'] ?? '';
        $reportInputDTO->type = $data['type'] ?? '';
        $reportInputDTO->parameters = $data['parameters'] ?? [];
        $reportInputDTO->result = $data['result'] ?? null;
        $reportInputDTO->createdById = $data['createdById'] ?? '';
        $reportInputDTO->scheduled = $data['scheduled'] ?? false;
        $reportInputDTO->cronExpression = $data['cronExpression'] ?? null;

        // Validate the DTO (optional, can be added later)

        $createdBy = $this->userRepository->find($reportInputDTO->createdById);
        if (!$createdBy) {
            return $this->json(['message' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }

        $report = new Report();
        $report->setName($reportInputDTO->name);
        $report->setType($reportInputDTO->type);
        $report->setParameters($reportInputDTO->parameters);
        $report->setResult($reportInputDTO->result);
        $report->setCreatedBy($createdBy);
        $report->setScheduled($reportInputDTO->scheduled);
        $report->setCronExpression($reportInputDTO->cronExpression);

        $errors = $this->validator->validate($report);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $this->json(ReportOutputDTO::createFromEntity($report), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $report = $this->reportRepository->find($id);

        if (!$report) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Create and populate ReportInputDTO with only the fields that are present in the request
        $reportInputDTO = new ReportInputDTO();

        if (isset($data['name'])) {
            $reportInputDTO->name = $data['name'];
            $report->setName($reportInputDTO->name);
        }

        if (isset($data['type'])) {
            $reportInputDTO->type = $data['type'];
            $report->setType($reportInputDTO->type);
        }

        if (isset($data['parameters'])) {
            $reportInputDTO->parameters = $data['parameters'];
            $report->setParameters($reportInputDTO->parameters);
        }

        if (array_key_exists('result', $data)) {
            $reportInputDTO->result = $data['result'];
            $report->setResult($reportInputDTO->result);
        }

        if (isset($data['createdById'])) {
            $reportInputDTO->createdById = $data['createdById'];
            $createdBy = $this->userRepository->find($reportInputDTO->createdById);
            if (!$createdBy) {
                return $this->json(['message' => 'User not found'], Response::HTTP_BAD_REQUEST);
            }
            $report->setCreatedBy($createdBy);
        }

        if (isset($data['scheduled'])) {
            $reportInputDTO->scheduled = $data['scheduled'];
            $report->setScheduled($reportInputDTO->scheduled);
        }

        if (array_key_exists('cronExpression', $data)) {
            $reportInputDTO->cronExpression = $data['cronExpression'];
            $report->setCronExpression($reportInputDTO->cronExpression);
        }

        $errors = $this->validator->validate($report);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(ReportOutputDTO::createFromEntity($report));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $report = $this->reportRepository->find($id);

        if (!$report) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($report);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/types', methods: ['GET'])]
    public function getTypes(): JsonResponse
    {
        // This is a placeholder for the actual implementation
        // In a real application, this would return the available report types
        $types = ['financial', 'penalty', 'team', 'user'];

        return $this->json($types);
    }

    #[Route('/{id}/generate', methods: ['POST'])]
    public function generate(string $id): JsonResponse
    {
        $report = $this->reportRepository->find($id);

        if (!$report) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        // Dispatch a message to generate the report asynchronously
        $this->messageBus->dispatch(new ReportGenerationMessage(
            $report->getId()->toString()
        ));

        return $this->json([
            'message' => 'Report generation has been queued',
            'report' => ReportOutputDTO::createFromEntity($report)
        ]);
    }

    #[Route('/{id}/schedule', methods: ['POST'])]
    public function schedule(string $id, Request $request): JsonResponse
    {
        $report = $this->reportRepository->find($id);

        if (!$report) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Create and populate ReportInputDTO with only the fields that are present in the request
        $reportInputDTO = new ReportInputDTO();
        $reportInputDTO->cronExpression = $data['cronExpression'] ?? '0 0 * * *'; // Default: daily at midnight

        $report->setScheduled(true);
        $report->setCronExpression($reportInputDTO->cronExpression);

        $this->entityManager->flush();

        return $this->json(ReportOutputDTO::createFromEntity($report));
    }
}
