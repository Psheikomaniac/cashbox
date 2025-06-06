<?php

namespace App\ApiPlatform\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\Payment\CreatePaymentDTO;
use App\DTO\Payment\PaymentResponseDTO;
use App\DTO\Payment\UpdatePaymentDTO;
use App\Entity\Payment;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use App\Repository\TeamUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TeamUserRepository $teamUserRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object
    {
        // Handle creation
        if ($data instanceof CreatePaymentDTO) {
            return $this->createPayment($data);
        }

        // Handle update
        if ($data instanceof UpdatePaymentDTO && isset($uriVariables['id'])) {
            return $this->updatePayment($data, $uriVariables['id']);
        }

        throw new \InvalidArgumentException('Unsupported data type or operation');
    }

    private function createPayment(CreatePaymentDTO $dto): PaymentResponseDTO
    {
        $teamUser = $this->teamUserRepository->find($dto->teamUserId);

        if (!$teamUser) {
            throw new NotFoundHttpException('Team user not found');
        }

        try {
            $currency = CurrencyEnum::from($dto->currency);
        } catch (\ValueError $e) {
            throw new BadRequestHttpException('Invalid currency');
        }

        try {
            $type = PaymentTypeEnum::from($dto->type);
        } catch (\ValueError $e) {
            throw new BadRequestHttpException('Invalid payment type');
        }

        $payment = new Payment();
        $payment->setTeamUser($teamUser);
        $payment->setAmount($dto->amount);
        $payment->setCurrency($currency);
        $payment->setType($type);
        $payment->setDescription($dto->description);
        $payment->setReference($dto->reference);

        if ($payment->requiresReference() && !$payment->getReference()) {
            throw new BadRequestHttpException('Reference is required for this payment type');
        }

        $errors = $this->validator->validate($payment);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return new PaymentResponseDTO(
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
        );
    }

    private function updatePayment(UpdatePaymentDTO $dto, string $id): PaymentResponseDTO
    {
        $payment = $this->entityManager->getRepository(Payment::class)->find($id);

        if (!$payment) {
            throw new NotFoundHttpException('Payment not found');
        }

        if ($dto->amount !== null) {
            $payment->setAmount($dto->amount);
        }

        if ($dto->currency !== null) {
            try {
                $currency = CurrencyEnum::from($dto->currency);
                $payment->setCurrency($currency);
            } catch (\ValueError $e) {
                throw new BadRequestHttpException('Invalid currency');
            }
        }

        if ($dto->type !== null) {
            try {
                $type = PaymentTypeEnum::from($dto->type);
                $payment->setType($type);
            } catch (\ValueError $e) {
                throw new BadRequestHttpException('Invalid payment type');
            }
        }

        if ($dto->description !== null) {
            $payment->setDescription($dto->description);
        }

        if ($dto->reference !== null) {
            $payment->setReference($dto->reference);
        }

        if ($payment->requiresReference() && !$payment->getReference()) {
            throw new BadRequestHttpException('Reference is required for this payment type');
        }

        $errors = $this->validator->validate($payment);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $this->entityManager->flush();

        return new PaymentResponseDTO(
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
        );
    }
}
