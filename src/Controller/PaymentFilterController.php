<?php

namespace App\Controller;

use App\DTO\Payment\PaymentResponseDTO;
use App\Entity\Payment;
use App\Repository\PaymentRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/payments', name: 'api_payments_')]
class PaymentFilterController extends AbstractController
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly TeamRepository $teamRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('/team/{teamId}', name: 'by_team', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getByTeam(string $teamId): JsonResponse
    {
        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $payments = $this->paymentRepository->findByTeam($team);
        $paymentDTOs = array_map(
            fn (Payment $payment) => new PaymentResponseDTO(
                $payment->getId()->toString(),
                $payment->getTeamUser()->getId()->toString(),
                $payment->getTeamUser()->getUser()->getName(),
                $payment->getTeamUser()->getTeam()->getName(),
                $payment->getAmount(),
                $payment->getCurrency()->value,
                $payment->getType()->value,
                $payment->getDescription(),
                $payment->getReference(),
                $payment->getCreatedAt(),
                $payment->getUpdatedAt()
            ),
            $payments
        );

        return $this->json($paymentDTOs);
    }

    #[Route('/user/{userId}', name: 'by_user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getByUser(string $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $payments = $this->paymentRepository->findByUser($user);
        $paymentDTOs = array_map(
            fn (Payment $payment) => new PaymentResponseDTO(
                $payment->getId()->toString(),
                $payment->getTeamUser()->getId()->toString(),
                $payment->getTeamUser()->getUser()->getName(),
                $payment->getTeamUser()->getTeam()->getName(),
                $payment->getAmount(),
                $payment->getCurrency()->value,
                $payment->getType()->value,
                $payment->getDescription(),
                $payment->getReference(),
                $payment->getCreatedAt(),
                $payment->getUpdatedAt()
            ),
            $payments
        );

        return $this->json($paymentDTOs);
    }
}
