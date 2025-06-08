<?php

namespace App\DTO;

/**
 * Interface für alle DTOs.
 *
 * Dieses Interface definiert die grundlegenden Methoden, die alle DTOs implementieren müssen.
 */
interface DTOInterface
{
    /**
     * Konvertiert DTO in ein assoziatives Array.
     *
     * @return array Das DTO als assoziatives Array.
     */
    public function toArray(): array;

    /**
     * Erstellt ein DTO aus einem assoziativen Array.
     *
     * @param array $data Die Daten, aus denen das DTO erstellt werden soll.
     * @return static Eine neue Instanz des DTOs.
     */
    public static function fromArray(array $data): static;

    /**
     * Erstellt ein DTO aus einer Entity.
     *
     * @param object $entity Die Entity, aus der das DTO erstellt werden soll.
     * @return static Eine neue Instanz des DTOs.
     */
    public static function fromEntity(object $entity): static;
}
