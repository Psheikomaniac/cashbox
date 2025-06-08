<?php

namespace App\DTO;

/**
 * Abstrakte Basisklasse für alle Response-DTOs.
 *
 * Diese Klasse implementiert die grundlegenden Methoden, die alle Response-DTOs haben sollten.
 * Alle Response-DTOs sollten von dieser Klasse erben, um eine konsistente Implementierung zu gewährleisten.
 */
abstract readonly class AbstractResponseDTO extends AbstractDTO
{
    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data): static
    {
        throw new \LogicException('Response-DTOs können nicht aus Arrays erstellt werden.');
    }
}
