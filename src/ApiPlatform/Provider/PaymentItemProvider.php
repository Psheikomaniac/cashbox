<?php

namespace App\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\Payment\PaymentResponseDTO;
use App\Repository\PaymentRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentItemProvider implements ProviderInterface
{
    public function __construct(private readonly PaymentRepository $paymentRepository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $id = $uriVariables['id'] ?? null;

        if (!$id) {
            throw new NotFoundHttpException('Payment ID is required');
        }

        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            throw new NotFoundHttpException('Payment not found');
        }

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
