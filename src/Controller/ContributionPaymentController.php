<?php

namespace App\Controller;

use App\DTO\ContributionPaymentOutputDTO;
use App\Entity\ContributionPayment;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use App\Repository\ContributionPaymentRepository;
use App\Repository\ContributionRepository;
use App\ValueObject\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/contribution-payments')]
class ContributionPaymentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContributionPaymentRepository $paymentRepository,
        private ContributionRepository $contributionRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $payments = $this->paymentRepository->findAll();

        return $this->json($payments);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            return $this->json(['message' => 'Contribution payment not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($payment);
    }

    #[Route('/contribution/{contributionId}', methods: ['GET'])]
    public function getByContribution(string $contributionId): JsonResponse
    {
        $contribution = $this->contributionRepository->find($contributionId);
        if (!$contribution) {
            return $this->json(['message' => 'Contribution not found'], Response::HTTP_NOT_FOUND);
        }

        $payments = $this->paymentRepository->findByContribution($contribution);

        return $this->json($payments);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $contribution = $this->contributionRepository->find($data['contributionId']);
        if (!$contribution) {
            return $this->json(['message' => 'Contribution not found'], Response::HTTP_BAD_REQUEST);
        }

        $currency = CurrencyEnum::tryFrom($data['currency'] ?? 'EUR') ?? CurrencyEnum::EUR;
        $money = new Money($data['amount'], $currency);
        $paymentMethod = isset($data['paymentMethod']) ? PaymentTypeEnum::tryFrom($data['paymentMethod']) : null;
        
        $payment = new ContributionPayment(
            contribution: $contribution,
            amount: $money,
            paymentMethod: $paymentMethod,
            reference: $data['reference'] ?? null,
            notes: $data['notes'] ?? null
        );

        $errors = $this->validator->validate($payment);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($payment);

        // Auto-mark contribution as paid if payment amount covers full contribution
        if ($contribution->getPaidAt() === null && $payment->getAmount()->getCents() >= $contribution->getAmount()->getCents()) {
            $contribution->pay();
        }

        $this->entityManager->flush();

        return $this->json($payment, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            return $this->json(['message' => 'Contribution payment not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // In rich domain model, contribution cannot be changed after creation
        
        // Only certain fields can be updated
        $paymentMethod = isset($data['paymentMethod']) ? PaymentTypeEnum::tryFrom($data['paymentMethod']) : null;
        
        $payment->update(
            paymentMethod: $paymentMethod,
            reference: $data['reference'] ?? null,
            notes: $data['notes'] ?? null
        );
        
        // Note: Amount and currency cannot be changed after creation for audit reasons

        $errors = $this->validator->validate($payment);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        // Note: Payment status updates are handled through the domain model
        // The contribution's payment status is managed through its own business logic

        return $this->json($payment);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            return $this->json(['message' => 'Contribution payment not found'], Response::HTTP_NOT_FOUND);
        }

        $contribution = $payment->getContribution();

        $this->entityManager->remove($payment);
        $this->entityManager->flush();

        // Recalculate total paid amount and update contribution's paidAt date if needed
        $totalPaid = $this->paymentRepository->getTotalPaidAmount($contribution);

        // Note: In rich domain model, we typically don't allow "unpaying" contributions
        // for audit reasons. If payment is removed and total becomes insufficient,
        // the paid status should be handled through business logic
        if ($totalPaid->getCents() < $contribution->getAmount()->getCents() && $contribution->getPaidAt() !== null) {
            // For now, we'll keep the anemic setter until domain method is implemented
            $contribution->setPaidAt(null);
            $this->entityManager->flush();
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
