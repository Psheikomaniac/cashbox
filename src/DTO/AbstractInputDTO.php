<?php

namespace App\DTO;

/**
 * Abstrakte Basisklasse für alle Input-DTOs.
 *
 * Diese Klasse implementiert die grundlegenden Methoden, die alle Input-DTOs haben sollten.
 * Alle Input-DTOs sollten von dieser Klasse erben, um eine konsistente Implementierung zu gewährleisten.
 */
abstract readonly class AbstractInputDTO extends AbstractDTO
{
    /**
     * {@inheritdoc}
     */
    public static function fromEntity(object $entity): static
    {
        throw new \LogicException('Input-DTOs können nicht aus Entities erstellt werden.');
    }
}
