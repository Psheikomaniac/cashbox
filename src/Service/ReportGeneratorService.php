<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report;
use App\Enum\ReportTypeEnum;
use App\Repository\PaymentRepository;
use App\Repository\PenaltyRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;

/**
 * Service for generating various types of reports.
 */
final class ReportGeneratorService
{
    public function __construct(
        private readonly PenaltyRepository $penaltyRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly UserRepository $userRepository,
        private readonly TeamRepository $teamRepository,
    ) {}

    public function generate(Report $report): array
    {
        $parameters = $report->getParameters();
        
        return match ($report->getType()) {
            ReportTypeEnum::FINANCIAL => $this->generateFinancialReport($parameters),
            ReportTypeEnum::PENALTY_SUMMARY => $this->generatePenaltySummaryReport($parameters),
            ReportTypeEnum::USER_ACTIVITY => $this->generateUserActivityReport($parameters),
            ReportTypeEnum::TEAM_OVERVIEW => $this->generateTeamOverviewReport($parameters),
            ReportTypeEnum::PAYMENT_HISTORY => $this->generatePaymentHistoryReport($parameters),
            ReportTypeEnum::AUDIT_LOG => $this->generateAuditLogReport($parameters),
        };
    }

    private function generateFinancialReport(array $parameters): array
    {
        $dateFrom = new \DateTimeImmutable($parameters['dateFrom']);
        $dateTo = new \DateTimeImmutable($parameters['dateTo']);
        $teamId = $parameters['teamId'] ?? null;

        // Get data using proper repository methods
        // Note: These methods would need to be implemented in the repositories
        $penalties = $this->penaltyRepository->findByDateRange($dateFrom, $dateTo);
        $payments = $this->paymentRepository->findByDateRange($dateFrom, $dateTo);

        if ($teamId) {
            $penalties = array_filter($penalties, fn($p) => $p->getTeamUser()->getTeam()->getId()->toString() === $teamId);
            $payments = array_filter($payments, fn($p) => $p->getTeamUser()->getTeam()->getId()->toString() === $teamId);
        }

        // Calculate metrics
        $totalPenalties = array_sum(array_map(fn($p) => $p->getAmount(), $penalties));
        $totalPayments = array_sum(array_map(fn($p) => $p->getAmount(), $payments));
        $netBalance = $totalPenalties - $totalPayments;

        return [
            'reportType' => 'financial',
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'summary' => [
                'totalPenalties' => $totalPenalties,
                'totalPayments' => $totalPayments,
                'netBalance' => $netBalance,
                'penaltyCount' => count($penalties),
                'paymentCount' => count($payments),
                'collectionRate' => $totalPenalties > 0 
                    ? round(($totalPayments / $totalPenalties) * 100, 2) 
                    : 100,
            ],
            'generatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    private function generatePenaltySummaryReport(array $parameters): array
    {
        $dateFrom = new \DateTimeImmutable($parameters['dateFrom']);
        $dateTo = new \DateTimeImmutable($parameters['dateTo']);
        $teamId = $parameters['teamId'] ?? null;

        $penalties = $this->penaltyRepository->findByDateRange($dateFrom, $dateTo);

        if ($teamId) {
            $penalties = array_filter($penalties, fn($p) => $p->getTeamUser()->getTeam()->getId()->toString() === $teamId);
        }

        return [
            'reportType' => 'penalty_summary',
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'summary' => [
                'totalPenalties' => count($penalties),
                'totalAmount' => array_sum(array_map(fn($p) => $p->getAmount(), $penalties)),
            ],
            'penalties' => array_map(fn($p) => [
                'id' => $p->getId()->toString(),
                'type' => $p->getType()->getName(),
                'amount' => $p->getAmount(),
                'reason' => $p->getReason(),
                'user' => $p->getTeamUser()->getUser()->getPersonName()->getFullName(),
                'date' => $p->getCreatedAt()->format('Y-m-d'),
                'paid' => $p->isPaid(),
            ], $penalties),
            'generatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    private function generateUserActivityReport(array $parameters): array
    {
        $userId = $parameters['userId'];
        $dateFrom = new \DateTimeImmutable($parameters['dateFrom']);
        $dateTo = new \DateTimeImmutable($parameters['dateTo']);

        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        // Get user penalties and payments for the date range
        $allPenalties = $this->penaltyRepository->findByDateRange($dateFrom, $dateTo);
        $allPayments = $this->paymentRepository->findByDateRange($dateFrom, $dateTo);

        $userPenalties = array_filter($allPenalties, fn($p) => $p->getTeamUser()->getUser() === $user);
        $userPayments = array_filter($allPayments, fn($p) => $p->getTeamUser()->getUser() === $user);

        return [
            'reportType' => 'user_activity',
            'user' => [
                'id' => $user->getId()->toString(),
                'name' => $user->getPersonName()->getFullName(),
                'email' => $user->getEmail()->getValue(),
            ],
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'summary' => [
                'penaltyCount' => count($userPenalties),
                'paymentCount' => count($userPayments),
                'totalPenalties' => array_sum(array_map(fn($p) => $p->getAmount(), $userPenalties)),
                'totalPayments' => array_sum(array_map(fn($p) => $p->getAmount(), $userPayments)),
            ],
            'generatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    private function generateTeamOverviewReport(array $parameters): array
    {
        $teamId = $parameters['teamId'];
        $team = $this->teamRepository->find($teamId);
        
        if (!$team) {
            throw new \InvalidArgumentException('Team not found');
        }

        // Get all penalties and payments for this team
        $penalties = $this->penaltyRepository->findAll();
        $payments = $this->paymentRepository->findAll();

        $teamPenalties = array_filter($penalties, fn($p) => $p->getTeamUser()->getTeam() === $team);
        $teamPayments = array_filter($payments, fn($p) => $p->getTeamUser()->getTeam() === $team);

        return [
            'reportType' => 'team_overview',
            'team' => [
                'id' => $team->getId()->toString(),
                'name' => $team->getName(),
                'status' => $team->isActive() ? 'active' : 'inactive',
            ],
            'summary' => [
                'totalPenalties' => array_sum(array_map(fn($p) => $p->getAmount(), $teamPenalties)),
                'totalPayments' => array_sum(array_map(fn($p) => $p->getAmount(), $teamPayments)),
                'penaltyCount' => count($teamPenalties),
                'paymentCount' => count($teamPayments),
            ],
            'generatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    private function generatePaymentHistoryReport(array $parameters): array
    {
        $dateFrom = new \DateTimeImmutable($parameters['dateFrom']);
        $dateTo = new \DateTimeImmutable($parameters['dateTo']);
        $userId = $parameters['userId'] ?? null;

        $payments = $this->paymentRepository->findByDateRange($dateFrom, $dateTo);

        if ($userId) {
            $user = $this->userRepository->find($userId);
            $payments = array_filter($payments, fn($p) => $p->getTeamUser()->getUser() === $user);
        }

        return [
            'reportType' => 'payment_history',
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'payments' => array_map(fn($p) => [
                'id' => $p->getId()->toString(),
                'user' => $p->getTeamUser()->getUser()->getPersonName()->getFullName(),
                'amount' => $p->getAmount(),
                'type' => $p->getPaymentType()?->getLabel() ?? 'Unknown',
                'date' => $p->getCreatedAt()->format('Y-m-d H:i:s'),
            ], $payments),
            'summary' => [
                'totalPayments' => count($payments),
                'totalAmount' => array_sum(array_map(fn($p) => $p->getAmount(), $payments)),
            ],
            'generatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    private function generateAuditLogReport(array $parameters): array
    {
        $dateFrom = new \DateTimeImmutable($parameters['dateFrom']);
        $dateTo = new \DateTimeImmutable($parameters['dateTo']);

        // Placeholder for audit log implementation
        return [
            'reportType' => 'audit_log',
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'events' => [], // Would be populated from audit log system
            'summary' => [
                'totalEvents' => 0,
                'userActions' => 0,
                'systemEvents' => 0,
            ],
            'generatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }
}
