<?php

namespace App\Event;

use App\Entity\ContributionType;

readonly class ContributionTypeCreatedEvent
{
    public function __construct(
        public ContributionType $contributionType
    ) {}
}