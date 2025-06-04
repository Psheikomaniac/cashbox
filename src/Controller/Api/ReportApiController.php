<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Report;
use App\Enum\ReportTypeEnum;
use App\Repository\ReportRepository;
use App\Service\ReportGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/reports', name: 'api_reports_')]
class ReportApiController extends AbstractController
{
    public function __construct(
        private ReportRepository $reportRepository,
        private ReportGeneratorService $reportGenerator,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));
        $type = $request->query->get('type');
        $status = $request->query->get('status');

        $filters = array_filter([
            'type' => $type,
            'status' => $status
        ]);

        $reports = $this->reportRepository->findPaginated($filters, $page, $limit);
        $total = $this->reportRepository->countFiltered($filters);

        return new JsonResponse([
            'data' => $this->serializer->normalize($reports, 'json', ['groups' => ['report:read']]),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $report = $this->reportRepository->find($id);
        
        if (!$report) {
            return new JsonResponse(['error' => 'Report not found'], 404);
        }

        return new JsonResponse(
            $this->serializer->normalize($report, 'json', ['groups' => ['report:read']])
        );
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        // Validate required fields
        if (!isset($data['type']) || !isset($data['name'])) {
            return new JsonResponse(['error' => 'Missing required fields: type, name'], 400);
        }

        try {
            $reportType = ReportTypeEnum::from($data['type']);
        } catch (\ValueError) {
            return new JsonResponse(['error' => 'Invalid report type'], 400);
        }

        try {
            $report = new Report();
            $report->setType($reportType);
            $report->setName($data['name']);
            $report->setDescription($data['description'] ?? '');
            
            if (isset($data['parameters'])) {
                $report->setParameters($data['parameters']);
            }

            $this->entityManager->persist($report);
            $this->entityManager->flush();

            return new JsonResponse(
                $this->serializer->normalize($report, 'json', ['groups' => ['report:read']]),
                201
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to create report: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/generate', name: 'generate', methods: ['POST'])]
    public function generate(string $id): JsonResponse
    {
        $report = $this->reportRepository->find($id);
        
        if (!$report) {
            return new JsonResponse(['error' => 'Report not found'], 404);
        }

        try {
            $this->reportGenerator->generateAsync($report);
            
            return new JsonResponse([
                'message' => 'Report generation started',
                'report_id' => $report->getId(),
                'status' => 'processing'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to start report generation: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/download', name: 'download', methods: ['GET'])]
    public function download(string $id): Response
    {
        $report = $this->reportRepository->find($id);
        
        if (!$report) {
            return new JsonResponse(['error' => 'Report not found'], 404);
        }

        if (!$report->isGenerated()) {
            return new JsonResponse(['error' => 'Report not yet generated'], 400);
        }

        $filePath = $report->getFilePath();
        if (!$filePath || !file_exists($filePath)) {
            return new JsonResponse(['error' => 'Report file not found'], 404);
        }

        return $this->file($filePath, $report->getName() . '.csv');
    }

    #[Route('/types', name: 'types', methods: ['GET'])]
    public function getTypes(): JsonResponse
    {
        $types = [];
        foreach (ReportTypeEnum::cases() as $type) {
            $types[] = [
                'value' => $type->value,
                'label' => $type->name
            ];
        }

        return new JsonResponse($types);
    }
}