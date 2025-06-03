<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Report;
use App\Service\ReportGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
final class GenerateReportController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReportGeneratorService $reportGenerator,
        private readonly MessageBusInterface $messageBus,
    ) {}

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Report $report, Request $request): JsonResponse
    {
        // Check if report is already being generated
        if ($report->getResult() !== null) {
            return new JsonResponse([
                'message' => 'Report has already been generated',
                'reportId' => $report->getId()->toString(),
                'status' => 'completed',
            ], 200);
        }

        try {
            // For async reports (long-running), dispatch to message queue
            if ($report->getType()->requiresAsync()) {
                $this->messageBus->dispatch(new \App\Message\ReportGenerationMessage(
                    $report->getId()->toString()
                ));

                return new JsonResponse([
                    'message' => 'Report generation started asynchronously',
                    'reportId' => $report->getId()->toString(),
                    'status' => 'processing',
                    'estimatedTime' => $report->getType()->getEstimatedExecutionTime(),
                ], 202);
            }

            // For sync reports (quick), generate immediately
            $result = $this->reportGenerator->generate($report);
            $report->generate($result);
            
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Report generated successfully',
                'reportId' => $report->getId()->toString(),
                'status' => 'completed',
                'resultCount' => count($result),
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to generate report',
                'message' => $e->getMessage(),
                'reportId' => $report->getId()->toString(),
            ], 500);
        }
    }
}