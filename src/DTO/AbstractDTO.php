<?php

namespace App\DTO;

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
     */
    abstract public static function fromArray(array $data): self;
}
