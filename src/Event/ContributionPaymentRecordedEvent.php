<?php

namespace App\Event;

use App\Entity\ContributionPayment;

readonly class ContributionPaymentRecordedEvent
{
    public function __construct(
        public ContributionPayment $contributionPayment
    ) {}
}