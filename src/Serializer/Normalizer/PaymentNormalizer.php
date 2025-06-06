<?php

namespace App\Serializer\Normalizer;

use App\Entity\Payment;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PaymentNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        if (!$object instanceof Payment) {
            throw new \InvalidArgumentException('The object must be an instance of ' . Payment::class);
        }

        return [
            'id' => $object->getId()->toString(),
            'amount' => $object->getFormattedAmount(),
            'rawAmount' => $object->getAmount(),
            'currency' => $object->getCurrency()->value,
            'type' => $object->getType()->value,
            'description' => $object->getDescription(),
            'reference' => $object->getReference(),
            'teamUser' => [
                'id' => $object->getTeamUser()->getId()->toString(),
                'user' => [
                    'id' => $object->getTeamUser()->getUser()->getId()->toString(),
                    'name' => $object->getTeamUser()->getUser()->getName()->getFullName()
                ],
                'team' => [
                    'id' => $object->getTeamUser()->getTeam()->getId()->toString(),
                    'name' => $object->getTeamUser()->getTeam()->getName()
                ]
            ],
            'createdAt' => $object->getCreatedAt()->format('c'),
            'updatedAt' => $object->getUpdatedAt()->format('c')
        ];
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Payment;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Payment::class => true,
        ];
    }
}
