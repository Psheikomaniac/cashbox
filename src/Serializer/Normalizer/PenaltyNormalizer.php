<?php

namespace App\Serializer\Normalizer;

use App\Entity\Penalty;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PenaltyNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        if (!$object instanceof Penalty) {
            throw new \InvalidArgumentException('The object must be an instance of ' . Penalty::class);
        }

        return [
            'id' => $object->getId()->toString(),
            'reason' => $object->getReason(),
            'amount' => $object->getAmount(),
            'formattedAmount' => $object->getFormattedAmount(),
            'currency' => $object->getCurrency()->value,
            'isPaid' => $object->isPaid(),
            'paidAt' => $object->getPaidAt()?->format('c'),
            'isArchived' => $object->isArchived(),
            'type' => [
                'id' => $object->getType()->getId()->toString(),
                'name' => $object->getType()->getName(),
                'description' => $object->getType()->getDescription(),
                'type' => $object->getType()->getType()->value,
                'defaultAmount' => $object->getType()->getDefaultAmount(),
                'isDrink' => $object->getType()->isDrink()
            ],
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
        return $data instanceof Penalty;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Penalty::class => true,
        ];
    }
}
