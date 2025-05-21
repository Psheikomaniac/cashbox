<?php

namespace App\Controller;

use App\Repository\PaymentRepository;
use App\Repository\PenaltyRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboards')]
class DashboardController extends AbstractController
{
    private PenaltyRepository $penaltyRepository;
    private PaymentRepository $paymentRepository;
    private UserRepository $userRepository;

    public function __construct(
        PenaltyRepository $penaltyRepository,
        PaymentRepository $paymentRepository,
        UserRepository $userRepository
    ) {
        $this->penaltyRepository = $penaltyRepository;
        $this->paymentRepository = $paymentRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('/user', methods: ['GET'])]
    public function getUserDashboard(Request $request): JsonResponse
    {
        // In a real implementation, we would get the current user from the security context
        // For now, we'll use a user ID from the request
        $userId = $request->query->get('userId');

        if (!$userId) {
            return $this->json(['error' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Get user penalties
        $penalties = $this->penaltyRepository->findByUser($userId);

        // Get user payments
        $payments = $this->paymentRepository->findByUser($userId);

        // Calculate outstanding balance
        $totalPenalties = array_reduce($penalties, function ($carry, $penalty) {
            return $carry + $penalty->getAmount();
        }, 0);

        $totalPayments = array_reduce($payments, function ($carry, $payment) {
            return $carry + $payment->getAmount();
        }, 0);

        $outstandingBalance = $totalPenalties - $totalPayments;

        return $this->json([
            'user' => [
                'id' => $user->getId()->toString(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName()
            ],
            'penalties' => [
                'count' => count($penalties),
                'total' => $totalPenalties
            ],
            'payments' => [
                'count' => count($payments),
                'total' => $totalPayments
            ],
            'outstandingBalance' => $outstandingBalance
        ]);
    }

    #[Route('/admin', methods: ['GET'])]
    public function getAdminDashboard(): JsonResponse
    {
        // Get all penalties
        $penalties = $this->penaltyRepository->findAll();

        // Get all payments
        $payments = $this->paymentRepository->findAll();

        // Get all users
        $users = $this->userRepository->findAll();

        // Calculate total penalties and payments
        $totalPenalties = array_reduce($penalties, function ($carry, $penalty) {
            return $carry + $penalty->getAmount();
        }, 0);

        $totalPayments = array_reduce($payments, function ($carry, $payment) {
            return $carry + $payment->getAmount();
        }, 0);

        // Calculate outstanding balance
        $outstandingBalance = $totalPenalties - $totalPayments;

        return $this->json([
            'users' => [
                'count' => count($users),
                'active' => count(array_filter($users, function ($user) {
                    return $user->isActive();
                }))
            ],
            'penalties' => [
                'count' => count($penalties),
                'total' => $totalPenalties
            ],
            'payments' => [
                'count' => count($payments),
                'total' => $totalPayments
            ],
            'outstandingBalance' => $outstandingBalance
        ]);
    }

    #[Route('/team/{teamId}', methods: ['GET'])]
    public function getTeamDashboard(string $teamId): JsonResponse
    {
        // Get team penalties
        $penalties = $this->penaltyRepository->findByTeam($teamId);

        // Get team payments
        $payments = $this->paymentRepository->findByTeam($teamId);

        // Calculate total penalties and payments
        $totalPenalties = array_reduce($penalties, function ($carry, $penalty) {
            return $carry + $penalty->getAmount();
        }, 0);

        $totalPayments = array_reduce($payments, function ($carry, $payment) {
            return $carry + $payment->getAmount();
        }, 0);

        // Calculate outstanding balance
        $outstandingBalance = $totalPenalties - $totalPayments;

        return $this->json([
            'team' => [
                'id' => $teamId
            ],
            'penalties' => [
                'count' => count($penalties),
                'total' => $totalPenalties
            ],
            'payments' => [
                'count' => count($payments),
                'total' => $totalPayments
            ],
            'outstandingBalance' => $outstandingBalance
        ]);
    }

    #[Route('/financial-overview', methods: ['GET'])]
    public function getFinancialOverview(Request $request): JsonResponse
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        // Convert dates to DateTime objects
        $startDateTime = $startDate ? new \DateTime($startDate) : null;
        $endDateTime = $endDate ? new \DateTime($endDate) : null;

        // Get penalties within date range
        $penalties = $startDateTime && $endDateTime
            ? $this->penaltyRepository->findByDateRange($startDateTime, $endDateTime)
            : $this->penaltyRepository->findAll();

        // Get payments within date range
        $payments = $startDateTime && $endDateTime
            ? $this->paymentRepository->findByDateRange($startDateTime, $endDateTime)
            : $this->paymentRepository->findAll();

        // Calculate total penalties and payments
        $totalPenalties = array_reduce($penalties, function ($carry, $penalty) {
            return $carry + $penalty->getAmount();
        }, 0);

        $totalPayments = array_reduce($payments, function ($carry, $payment) {
            return $carry + $payment->getAmount();
        }, 0);

        // Calculate outstanding balance
        $outstandingBalance = $totalPenalties - $totalPayments;

        return $this->json([
            'dateRange' => [
                'start' => $startDateTime ? $startDateTime->format('Y-m-d') : null,
                'end' => $endDateTime ? $endDateTime->format('Y-m-d') : null
            ],
            'penalties' => [
                'count' => count($penalties),
                'total' => $totalPenalties
            ],
            'payments' => [
                'count' => count($payments),
                'total' => $totalPayments
            ],
            'outstandingBalance' => $outstandingBalance
        ]);
    }
}
