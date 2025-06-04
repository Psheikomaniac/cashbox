<?php

namespace App\Controller;

use App\DTO\PaymentInputDTO;
use App\DTO\PaymentOutputDTO;
use App\Entity\Payment;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use App\Repository\PaymentRepository;
use App\Repository\TeamRepository;
use App\Repository\TeamUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/payments')]
class PaymentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaymentRepository $paymentRepository,
        private TeamRepository $teamRepository,
        private UserRepository $userRepository,
        private TeamUserRepository $teamUserRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $payments = $this->paymentRepository->findAll();
        $paymentDTOs = array_map(fn (Payment $payment) => PaymentOutputDTO::createFromEntity($payment), $payments);

        return $this->json($paymentDTOs);
    }

    #[Route('/team/{teamId}', methods: ['GET'])]
    public function getByTeam(string $teamId): JsonResponse
    {
        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $payments = $this->paymentRepository->findByTeam($team);
        $paymentDTOs = array_map(fn (Payment $payment) => PaymentOutputDTO::createFromEntity($payment), $payments);

        return $this->json($paymentDTOs);
    }

    #[Route('/user/{userId}', methods: ['GET'])]
    public function getByUser(string $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $payments = $this->paymentRepository->findByUser($user);
        $paymentDTOs = array_map(fn (Payment $payment) => PaymentOutputDTO::createFromEntity($payment), $payments);

        return $this->json($paymentDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            return $this->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(PaymentOutputDTO::createFromEntity($payment));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Create and populate PaymentInputDTO
        $paymentInputDTO = new PaymentInputDTO();
        $paymentInputDTO->teamId = $data['teamId'] ?? '';
        $paymentInputDTO->userId = $data['userId'] ?? '';
        $paymentInputDTO->amount = $data['amount'] ?? 0;
        $paymentInputDTO->currency = $data['currency'] ?? 'EUR';
        $paymentInputDTO->type = $data['type'] ?? 'cash';
        $paymentInputDTO->description = $data['description'] ?? null;
        $paymentInputDTO->reference = $data['reference'] ?? null;

        // Validate the DTO (optional, can be added later)

        $teamUser = $this->findTeamUser($paymentInputDTO->teamId, $paymentInputDTO->userId);
        if (!$teamUser) {
            return $this->json(['message' => 'Team user not found'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $currency = CurrencyEnum::from($paymentInputDTO->currency);
        } catch (\ValueError $e) {
            return $this->json(['message' => 'Invalid currency'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $type = PaymentTypeEnum::from($paymentInputDTO->type);
        } catch (\ValueError $e) {
            return $this->json(['message' => 'Invalid payment type'], Response::HTTP_BAD_REQUEST);
        }

        $payment = new Payment();
        $payment->setTeamUser($teamUser);
        $payment->setAmount($paymentInputDTO->amount);
        $payment->setCurrency($currency);
        $payment->setType($type);
        $payment->setDescription($paymentInputDTO->description);
        $payment->setReference($paymentInputDTO->reference);

        if ($payment->requiresReference() && !$payment->getReference()) {
            return $this->json(['message' => 'Reference is required for this payment type'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($payment);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return $this->json(PaymentOutputDTO::createFromEntity($payment), Response::HTTP_CREATED);
    }


    private function findTeamUser(string $teamId, string $userId)
    {
        $team = $this->teamRepository->find($teamId);
        $user = $this->userRepository->find($userId);

        if (!$team || !$user) {
            return null;
        }

        return $this->teamUserRepository->findOneByTeamAndUser($team, $user);
    }
}
