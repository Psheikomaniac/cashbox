<?php

namespace App\Controller;

use App\DTO\Payment\CreatePaymentDTO;
use App\DTO\PaymentOutputDTO;
use App\Entity\Payment;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use App\Exception\ValidationException;
use App\Repository\PaymentRepository;
use App\Repository\TeamRepository;
use App\Repository\TeamUserRepository;
use App\Repository\UserRepository;
use App\Service\RequestValidator;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentRepository $paymentRepository,
        private readonly TeamRepository $teamRepository,
        private readonly UserRepository $userRepository,
        private readonly TeamUserRepository $teamUserRepository,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly RequestValidator $requestValidator
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
        try {
            // Validate request and convert to DTO
            $createPaymentDTO = $this->requestValidator->validateRequest($request, CreatePaymentDTO::class);

            // Find TeamUser by ID
            $teamUser = $this->teamUserRepository->find($createPaymentDTO->teamUserId);
            if (!$teamUser) {
                return $this->json(['message' => 'Team user not found'], Response::HTTP_BAD_REQUEST);
            }

            // Convert string values to enum instances
            $currency = CurrencyEnum::from($createPaymentDTO->currency);
            $type = PaymentTypeEnum::from($createPaymentDTO->type);

            // Create and populate Payment entity
            $payment = new Payment();
            $payment->setTeamUser($teamUser);
            $payment->setAmount($createPaymentDTO->amount);
            $payment->setCurrency($currency);
            $payment->setType($type);
            $payment->setDescription($createPaymentDTO->description);
            $payment->setReference($createPaymentDTO->reference);

            // Check if reference is required for this payment type
            if ($payment->requiresReference() && !$payment->getReference()) {
                return $this->json(['message' => 'Reference is required for this payment type'], Response::HTTP_BAD_REQUEST);
            }

            // Save payment
            $this->entityManager->persist($payment);
            $this->entityManager->flush();

            return $this->json(PaymentOutputDTO::createFromEntity($payment), Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
        } catch (\ValueError $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['message' => 'An error occurred: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
