<?php

namespace App\Event;

use App\Entity\ContributionType;

readonly class ContributionTypeUpdatedEvent
{
    public function __construct(
        public ContributionType $contributionType
    ) {}
}