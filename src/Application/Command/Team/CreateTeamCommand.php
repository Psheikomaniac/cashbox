<?php

namespace App\Application\Command\Team;

readonly class CreateTeamCommand
{
    public function __construct(
        public string $name,
        public string $description = '',
        public bool $active = true
    ) {}
}
