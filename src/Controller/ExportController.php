<?php

namespace App\Controller;

use App\Message\ExportGenerationMessage;
use App\Repository\PenaltyRepository;
use App\Repository\ReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/export')]
class ExportController extends AbstractController
{
    private PenaltyRepository $penaltyRepository;
    private ReportRepository $reportRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        PenaltyRepository $penaltyRepository,
        ReportRepository $reportRepository,
        MessageBusInterface $messageBus
    ) {
        $this->penaltyRepository = $penaltyRepository;
        $this->reportRepository = $reportRepository;
        $this->messageBus = $messageBus;
    }

    #[Route('/penalties', methods: ['POST'])]
    public function exportPenalties(Request $request): JsonResponse
    {
        $format = $request->request->get('format', 'pdf');
        $teamId = $request->request->get('teamId');
        $userId = $request->request->get('userId');
        $startDate = $request->request->get('startDate');
        $endDate = $request->request->get('endDate');

        // Generate a unique filename for the export
        $filename = 'penalties-' . uniqid() . '.' . $format;

        // Create filters array
        $filters = [
            'teamId' => $teamId,
            'userId' => $userId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        // Dispatch a message to generate the export asynchronously
        $this->messageBus->dispatch(new ExportGenerationMessage(
            'penalties',
            $format,
            $filename,
            null,
            $filters
        ));

        return $this->json([
            'success' => true,
            'message' => 'Export generation has been queued',
            'format' => $format,
            'filters' => $filters,
            'downloadUrl' => '/api/export/download/' . $filename
        ]);
    }

    #[Route('/reports/{id}', methods: ['POST'])]
    public function exportReport(string $id, Request $request): JsonResponse
    {
        $report = $this->reportRepository->find($id);

        if (!$report) {
            return $this->json(['error' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        $format = $request->request->get('format', 'pdf');

        // Generate a unique filename for the export
        $filename = 'report-' . $report->getId()->toString() . '.' . $format;

        // Dispatch a message to generate the export asynchronously
        $this->messageBus->dispatch(new ExportGenerationMessage(
            'report',
            $format,
            $filename,
            $report->getId()->toString()
        ));

        return $this->json([
            'success' => true,
            'message' => 'Report export has been queued',
            'format' => $format,
            'report' => [
                'id' => $report->getId()->toString(),
                'name' => $report->getName(),
                'type' => $report->getType()
            ],
            'downloadUrl' => '/api/export/download/' . $filename
        ]);
    }

    #[Route('/formats', methods: ['GET'])]
    public function getFormats(): JsonResponse
    {
        return $this->json([
            'formats' => [
                [
                    'id' => 'pdf',
                    'name' => 'PDF',
                    'mimeType' => 'application/pdf',
                    'extension' => 'pdf'
                ],
                [
                    'id' => 'xlsx',
                    'name' => 'Excel',
                    'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'extension' => 'xlsx'
                ],
                [
                    'id' => 'csv',
                    'name' => 'CSV',
                    'mimeType' => 'text/csv',
                    'extension' => 'csv'
                ]
            ]
        ]);
    }

    #[Route('/download/{filename}', methods: ['GET'])]
    public function download(string $filename): Response
    {
        // In a real implementation, we would generate and return the actual file
        // For now, we'll just return a mock response

        $response = new Response('Mock export file content');
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
