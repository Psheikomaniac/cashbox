<?php

namespace App\Event;

use App\Entity\Contribution;

readonly class ContributionCreatedEvent
{
    public function __construct(
        public Contribution $contribution
    ) {}
}