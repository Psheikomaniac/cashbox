<?php

namespace App\DTO;

interface DTOInterface
{
    /**
     * Konvertiert DTO in ein assoziatives Array.
     */
    public function toArray(): array;
}
