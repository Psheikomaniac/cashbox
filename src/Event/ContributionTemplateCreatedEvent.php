<?php

namespace App\Event;

use App\Entity\ContributionTemplate;

readonly class ContributionTemplateCreatedEvent
{
    public function __construct(
        public ContributionTemplate $contributionTemplate
    ) {}
}