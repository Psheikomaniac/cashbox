<?php

namespace App\Event;

use App\Entity\ContributionTemplate;

readonly class ContributionTemplateAppliedEvent
{
    public function __construct(
        public ContributionTemplate $contributionTemplate,
        public int $appliedCount
    ) {}
}