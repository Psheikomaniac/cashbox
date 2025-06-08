<?php

namespace App\DTO;

/**
 * Abstrakte Basisklasse für alle DTOs.
 *
 * Diese Klasse implementiert die grundlegenden Methoden, die alle DTOs haben sollten.
 * Alle DTOs sollten von dieser Klasse erben, um eine konsistente Implementierung zu gewährleisten.
 */
abstract readonly class AbstractDTO implements DTOInterface
{
    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Erstellt ein DTO aus einem assoziativen Array.
     *
     * @param array $data Die Daten, aus denen das DTO erstellt werden soll.
     * @return static Eine neue Instanz des DTOs.
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Erstellt ein DTO aus einer Entity.
     *
     * Diese Methode sollte in jeder konkreten DTO-Klasse implementiert werden,
     * um ein DTO aus einer Entity zu erstellen.
     *
     * @param object $entity Die Entity, aus der das DTO erstellt werden soll.
     * @return static Eine neue Instanz des DTOs.
     */
    abstract public static function fromEntity(object $entity): static;
}
