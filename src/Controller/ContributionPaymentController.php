<?php

namespace App\Controller;

use App\Entity\ContributionPayment;
use App\Repository\ContributionPaymentRepository;
use App\Repository\ContributionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/contribution-payments')]
class ContributionPaymentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContributionPaymentRepository $paymentRepository,
        private ContributionRepository $contributionRepository,
        private SerializerInterface $serializer,
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

        $payment = new ContributionPayment();
        $payment->setContribution($contribution);
        $payment->setAmount($data['amount']);
        $payment->setCurrency($data['currency'] ?? 'EUR');

        if (isset($data['paymentMethod'])) {
            $payment->setPaymentMethod($data['paymentMethod']);
        }

        if (isset($data['reference'])) {
            $payment->setReference($data['reference']);
        }

        if (isset($data['notes'])) {
            $payment->setNotes($data['notes']);
        }

        $errors = $this->validator->validate($payment);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($payment);

        // Update the contribution's paidAt date if this is the first payment
        // or if the total paid amount equals or exceeds the contribution amount
        $totalPaid = $this->paymentRepository->getTotalPaidAmount($contribution) + $payment->getAmount();
        if ($contribution->getPaidAt() === null && $totalPaid >= $contribution->getAmount()) {
            $contribution->setPaidAt(new \DateTimeImmutable());
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

        if (isset($data['contributionId'])) {
            $contribution = $this->contributionRepository->find($data['contributionId']);
            if (!$contribution) {
                return $this->json(['message' => 'Contribution not found'], Response::HTTP_BAD_REQUEST);
            }
            $payment->setContribution($contribution);
        }

        if (isset($data['amount'])) {
            $payment->setAmount($data['amount']);
        }

        if (isset($data['currency'])) {
            $payment->setCurrency($data['currency']);
        }

        if (array_key_exists('paymentMethod', $data)) {
            $payment->setPaymentMethod($data['paymentMethod']);
        }

        if (array_key_exists('reference', $data)) {
            $payment->setReference($data['reference']);
        }

        if (array_key_exists('notes', $data)) {
            $payment->setNotes($data['notes']);
        }

        $errors = $this->validator->validate($payment);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        // Recalculate total paid amount and update contribution's paidAt date if needed
        $contribution = $payment->getContribution();
        $totalPaid = $this->paymentRepository->getTotalPaidAmount($contribution);

        if ($totalPaid >= $contribution->getAmount() && $contribution->getPaidAt() === null) {
            $contribution->setPaidAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        } elseif ($totalPaid < $contribution->getAmount() && $contribution->getPaidAt() !== null) {
            $contribution->setPaidAt(null);
            $this->entityManager->flush();
        }

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

        if ($totalPaid < $contribution->getAmount() && $contribution->getPaidAt() !== null) {
            $contribution->setPaidAt(null);
            $this->entityManager->flush();
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
