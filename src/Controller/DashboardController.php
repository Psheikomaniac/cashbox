<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/dashboards', name: 'api_dashboards_')]
final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    #[Route('/user', name: 'user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserDashboard(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return new JsonResponse([
                'error' => 'Authentication required',
                'message' => 'User must be authenticated',
            ], 401);
        }

        try {
            $dashboardData = $this->dashboardService->getUserDashboard($user);

            return new JsonResponse([
                'dashboard' => $dashboardData,
                'userId' => $user->getId()->toString(),
                'generatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to load user dashboard',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/admin', name: 'admin', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getAdminDashboard(): JsonResponse
    {
        try {
            $dashboardData = $this->dashboardService->getAdminDashboard();

            return new JsonResponse([
                'dashboard' => $dashboardData,
                'generatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to load admin dashboard',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/team/{teamId}', name: 'team', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function getTeamDashboard(string $teamId): JsonResponse
    {
        try {
            $dashboardData = $this->dashboardService->getTeamDashboard($teamId);

            return new JsonResponse([
                'dashboard' => $dashboardData,
                'teamId' => $teamId,
                'generatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to load team dashboard',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/financial-overview', name: 'financial_overview', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getFinancialOverview(Request $request): JsonResponse
    {
        $dateFrom = $request->query->get('dateFrom');
        $dateTo = $request->query->get('dateTo');

        try {
            $overviewData = $this->dashboardService->getFinancialOverview($dateFrom, $dateTo);

            return new JsonResponse([
                'overview' => $overviewData,
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
                'generatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to load financial overview',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
