<?php

namespace App\Controller;

use App\DTO\ReportDTO;
use App\Entity\Report;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $reports = $this->reportRepository->findAll();
        $reportDTOs = array_map(fn (Report $report) => ReportDTO::createFromEntity($report), $reports);

        return $this->json($reportDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $report = $this->reportRepository->find($id);

        if (!$report) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(ReportDTO::createFromEntity($report));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $createdBy = $this->userRepository->find($data['createdById']);
        if (!$createdBy) {
            return $this->json(['message' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }

        $report = new Report();
        $report->setName($data['name']);
        $report->setType($data['type']);
        $report->setParameters($data['parameters'] ?? []);
        $report->setResult($data['result'] ?? null);
        $report->setCreatedBy($createdBy);
        $report->setScheduled($data['scheduled'] ?? false);
        $report->setCronExpression($data['cronExpression'] ?? null);

        $errors = $this->validator->validate($report);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $this->json(ReportDTO::createFromEntity($report), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $report = $this->reportRepository->find($id);

        if (!$report) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $report->setName($data['name']);
        }

        if (isset($data['type'])) {
            $report->setType($data['type']);
        }

        if (isset($data['parameters'])) {
            $report->setParameters($data['parameters']);
        }

        if (array_key_exists('result', $data)) {
            $report->setResult($data['result']);
        }

        if (isset($data['createdById'])) {
            $createdBy = $this->userRepository->find($data['createdById']);
            if (!$createdBy) {
                return $this->json(['message' => 'User not found'], Response::HTTP_BAD_REQUEST);
            }
            $report->setCreatedBy($createdBy);
        }

        if (isset($data['scheduled'])) {
            $report->setScheduled($data['scheduled']);
        }

        if (array_key_exists('cronExpression', $data)) {
            $report->setCronExpression($data['cronExpression']);
        }

        $errors = $this->validator->validate($report);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(ReportDTO::createFromEntity($report));
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

        // This is a placeholder for the actual implementation
        // In a real application, this would generate the report based on its type and parameters
        $result = ['generated' => true, 'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')];
        $report->setResult($result);

        $this->entityManager->flush();

        return $this->json(ReportDTO::createFromEntity($report));
    }

    #[Route('/{id}/schedule', methods: ['POST'])]
    public function schedule(string $id, Request $request): JsonResponse
    {
        $report = $this->reportRepository->find($id);

        if (!$report) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $report->setScheduled(true);
        $report->setCronExpression($data['cronExpression'] ?? '0 0 * * *'); // Default: daily at midnight

        $this->entityManager->flush();

        return $this->json(ReportDTO::createFromEntity($report));
    }
}
