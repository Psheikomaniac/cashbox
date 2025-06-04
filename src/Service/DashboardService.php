<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\PaymentRepository;
use App\Repository\PenaltyRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;

/**
 * Service for generating dashboard data and analytics.
 */
final class DashboardService
{
    public function __construct(
        private readonly PenaltyRepository $penaltyRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly UserRepository $userRepository,
        private readonly TeamRepository $teamRepository,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function getUserDashboard(User $user): array
    {
        // Get user penalties
        $penalties = $this->penaltyRepository->findByUser($user);
        $unpaidPenalties = $this->penaltyRepository->findUnpaidByUser($user);
        
        // Get user payments
        $payments = $this->paymentRepository->findByUser($user);
        
        // Get recent notifications
        $recentNotifications = $this->notificationRepository->findRecentForUser($user, 5);
        $unreadCount = $this->notificationRepository->countUnreadForUser($user);

        // Calculate totals
        $totalPenalties = array_sum(array_map(fn($p) => $p->getAmount(), $penalties));
        $totalPayments = array_sum(array_map(fn($p) => $p->getAmount(), $payments));
        $outstandingBalance = $totalPenalties - $totalPayments;

        return [
            'user' => [
                'id' => $user->getId()->toString(),
                'name' => $user->getPersonName()->getFullName(),
                'email' => $user->getEmail()->getValue(),
            ],
            'penalties' => [
                'total' => count($penalties),
                'unpaid' => count($unpaidPenalties),
                'totalAmount' => $totalPenalties,
                'recent' => array_slice($penalties, 0, 5),
            ],
            'payments' => [
                'total' => count($payments),
                'totalAmount' => $totalPayments,
                'recent' => array_slice($payments, 0, 5),
            ],
            'balance' => [
                'outstanding' => $outstandingBalance,
                'status' => $outstandingBalance > 0 ? 'outstanding' : 'paid_up',
            ],
            'notifications' => [
                'unreadCount' => $unreadCount,
                'recent' => $recentNotifications,
            ],
        ];
    }

    public function getAdminDashboard(): array
    {
        // Get aggregated data
        $totalUsers = $this->userRepository->count([]);
        $activeUsers = $this->userRepository->countActive();
        $totalTeams = $this->teamRepository->count([]);
        
        $allPenalties = $this->penaltyRepository->findAll();
        $unpaidPenalties = $this->penaltyRepository->findUnpaid();
        $allPayments = $this->paymentRepository->findAll();

        // Calculate financial metrics
        $totalPenaltyAmount = array_sum(array_map(fn($p) => $p->getAmount(), $allPenalties));
        $totalPaymentAmount = array_sum(array_map(fn($p) => $p->getAmount(), $allPayments));
        $outstandingAmount = array_sum(array_map(fn($p) => $p->getAmount(), $unpaidPenalties));

        // Get recent activity
        $recentPenalties = $this->penaltyRepository->findRecent(10);
        $recentPayments = $this->paymentRepository->findRecent(10);

        return [
            'overview' => [
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                ],
                'teams' => [
                    'total' => $totalTeams,
                ],
                'penalties' => [
                    'total' => count($allPenalties),
                    'unpaid' => count($unpaidPenalties),
                    'totalAmount' => $totalPenaltyAmount,
                    'outstandingAmount' => $outstandingAmount,
                ],
                'payments' => [
                    'total' => count($allPayments),
                    'totalAmount' => $totalPaymentAmount,
                ],
            ],
            'financial' => [
                'totalRevenue' => $totalPaymentAmount,
                'outstandingAmount' => $outstandingAmount,
                'collectionRate' => $totalPenaltyAmount > 0 
                    ? round(($totalPaymentAmount / $totalPenaltyAmount) * 100, 2) 
                    : 100,
            ],
            'recentActivity' => [
                'penalties' => $recentPenalties,
                'payments' => $recentPayments,
            ],
        ];
    }

    public function getOverview(?string $teamId = null, string $period = 'month'): array
    {
        // If team ID is provided, return team dashboard, otherwise admin dashboard
        if ($teamId) {
            return $this->getTeamDashboard($teamId);
        }
        
        return $this->getAdminDashboard();
    }

    public function getStatistics(?string $teamId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $penalties = $this->penaltyRepository->findAll();
        $payments = $this->paymentRepository->findAll();

        return [
            'penalties' => [
                'count' => count($penalties),
                'total_amount' => array_sum(array_map(fn($p) => $p->getAmount(), $penalties))
            ],
            'payments' => [
                'count' => count($payments),
                'total_amount' => array_sum(array_map(fn($p) => $p->getAmount(), $payments))
            ]
        ];
    }

    public function getRecentActivities(?string $teamId = null, int $limit = 10): array
    {
        $recentPenalties = $this->penaltyRepository->findRecent($limit);
        $recentPayments = $this->paymentRepository->findRecent($limit);

        return [
            'penalties' => $recentPenalties,
            'payments' => $recentPayments
        ];
    }

    public function getFinancialSummary(?string $teamId = null, int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        
        $penalties = $this->penaltyRepository->findAll();
        $payments = $this->paymentRepository->findAll();

        return [
            'year' => $year,
            'total_penalties' => array_sum(array_map(fn($p) => $p->getAmount(), $penalties)),
            'total_payments' => array_sum(array_map(fn($p) => $p->getAmount(), $payments)),
            'outstanding' => array_sum(array_map(fn($p) => $p->getAmount(), $this->penaltyRepository->findUnpaid()))
        ];
    }

    public function getTeamDashboard(string $teamId): array
    {
        $team = $this->teamRepository->find($teamId);
        
        if (!$team) {
            throw new \InvalidArgumentException('Team not found');
        }

        // Get team data
        $teamUsers = $team->getTeamUsers();
        $teamPenalties = $this->penaltyRepository->findByTeam($teamId);
        $teamPayments = $this->paymentRepository->findByTeam($teamId);

        // Calculate metrics
        $totalPenalties = array_sum(array_map(fn($p) => $p->getAmount(), $teamPenalties));
        $totalPayments = array_sum(array_map(fn($p) => $p->getAmount(), $teamPayments));
        $outstandingBalance = $totalPenalties - $totalPayments;

        // Get member statistics
        $memberStats = [];
        foreach ($teamUsers as $teamUser) {
            $user = $teamUser->getUser();
            $userPenalties = array_filter($teamPenalties, fn($p) => $p->getTeamUser() === $teamUser);
            $userPayments = array_filter($teamPayments, fn($p) => $p->getTeamUser() === $teamUser);
            
            $memberStats[] = [
                'user' => [
                    'id' => $user->getId()->toString(),
                    'name' => $user->getPersonName()->getFullName(),
                    'role' => $teamUser->getRole()->getLabel(),
                ],
                'penalties' => count($userPenalties),
                'payments' => count($userPayments),
                'balance' => array_sum(array_map(fn($p) => $p->getAmount(), $userPenalties)) - 
                           array_sum(array_map(fn($p) => $p->getAmount(), $userPayments)),
            ];
        }

        return [
            'team' => [
                'id' => $team->getId()->toString(),
                'name' => $team->getName(),
                'memberCount' => count($teamUsers),
                'status' => $team->isActive() ? 'active' : 'inactive',
            ],
            'financial' => [
                'totalPenalties' => $totalPenalties,
                'totalPayments' => $totalPayments,
                'outstandingBalance' => $outstandingBalance,
                'penaltyCount' => count($teamPenalties),
                'paymentCount' => count($teamPayments),
            ],
            'members' => $memberStats,
            'recentActivity' => [
                'penalties' => array_slice($teamPenalties, 0, 10),
                'payments' => array_slice($teamPayments, 0, 10),
            ],
        ];
    }

    public function getFinancialOverview(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $startDate = $dateFrom ? new \DateTimeImmutable($dateFrom) : null;
        $endDate = $dateTo ? new \DateTimeImmutable($dateTo) : null;

        // Get filtered data
        $penalties = $startDate && $endDate
            ? $this->penaltyRepository->findByDateRange($startDate, $endDate)
            : $this->penaltyRepository->findAll();

        $payments = $startDate && $endDate
            ? $this->paymentRepository->findByDateRange($startDate, $endDate)
            : $this->paymentRepository->findAll();

        // Calculate metrics
        $totalPenalties = array_sum(array_map(fn($p) => $p->getAmount(), $penalties));
        $totalPayments = array_sum(array_map(fn($p) => $p->getAmount(), $payments));
        
        // Group by month for trends
        $penaltyTrends = $this->groupByMonth($penalties);
        $paymentTrends = $this->groupByMonth($payments);

        return [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'summary' => [
                'penalties' => [
                    'count' => count($penalties),
                    'totalAmount' => $totalPenalties,
                    'averageAmount' => count($penalties) > 0 ? round($totalPenalties / count($penalties), 2) : 0,
                ],
                'payments' => [
                    'count' => count($payments),
                    'totalAmount' => $totalPayments,
                    'averageAmount' => count($payments) > 0 ? round($totalPayments / count($payments), 2) : 0,
                ],
                'netBalance' => $totalPenalties - $totalPayments,
                'collectionRate' => $totalPenalties > 0 
                    ? round(($totalPayments / $totalPenalties) * 100, 2) 
                    : 100,
            ],
            'trends' => [
                'penalties' => $penaltyTrends,
                'payments' => $paymentTrends,
            ],
        ];
    }

    private function groupByMonth(array $items): array
    {
        $grouped = [];
        
        foreach ($items as $item) {
            $month = $item->getCreatedAt()->format('Y-m');
            
            if (!isset($grouped[$month])) {
                $grouped[$month] = ['count' => 0, 'amount' => 0];
            }
            
            $grouped[$month]['count']++;
            $grouped[$month]['amount'] += $item->getAmount();
        }

        return $grouped;
    }
}