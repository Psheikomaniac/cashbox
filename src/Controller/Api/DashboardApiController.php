<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboard', name: 'api_dashboard_')]
class DashboardApiController extends AbstractController
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    #[Route('', name: 'overview', methods: ['GET'])]
    public function overview(Request $request): JsonResponse
    {
        $teamId = $request->query->get('team');
        $period = $request->query->get('period', 'month'); // month, quarter, year
        
        try {
            $data = $this->dashboardService->getOverview($teamId, $period);
            
            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to load dashboard: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/statistics', name: 'statistics', methods: ['GET'])]
    public function statistics(Request $request): JsonResponse
    {
        $teamId = $request->query->get('team');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');
        
        try {
            $data = $this->dashboardService->getStatistics($teamId, $startDate, $endDate);
            
            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to load statistics: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/recent-activities', name: 'recent_activities', methods: ['GET'])]
    public function recentActivities(Request $request): JsonResponse
    {
        $teamId = $request->query->get('team');
        $limit = min(50, max(1, (int) $request->query->get('limit', 10)));
        
        try {
            $data = $this->dashboardService->getRecentActivities($teamId, $limit);
            
            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to load activities: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/financial-summary', name: 'financial_summary', methods: ['GET'])]
    public function financialSummary(Request $request): JsonResponse
    {
        $teamId = $request->query->get('team');
        $year = (int) $request->query->get('year', date('Y'));
        
        try {
            $data = $this->dashboardService->getFinancialSummary($teamId, $year);
            
            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to load financial summary: ' . $e->getMessage()], 500);
        }
    }
}