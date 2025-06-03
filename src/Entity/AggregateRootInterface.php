<?php

namespace App\Entity;

interface AggregateRootInterface
{
    public function releaseEvents(): array;
}