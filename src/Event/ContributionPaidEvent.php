<?php

namespace App\Event;

use App\Entity\Contribution;

readonly class ContributionPaidEvent
{
    public function __construct(
        public Contribution $contribution
    ) {}
}