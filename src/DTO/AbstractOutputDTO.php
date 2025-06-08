<?php

namespace App\DTO;

/**
 * Abstrakte Basisklasse für alle Output-DTOs.
 *
 * Diese Klasse implementiert die grundlegenden Methoden, die alle Output-DTOs haben sollten.
 * Alle Output-DTOs sollten von dieser Klasse erben, um eine konsistente Implementierung zu gewährleisten.
 */
abstract readonly class AbstractOutputDTO extends AbstractDTO
{
    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data): static
    {
        throw new \LogicException('Output-DTOs können nicht aus Arrays erstellt werden.');
    }
}
