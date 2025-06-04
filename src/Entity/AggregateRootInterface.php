<?php

namespace App\Entity;

interface AggregateRootInterface
{
    public function getEvents(): array;
    public function clearEvents(): void;
}