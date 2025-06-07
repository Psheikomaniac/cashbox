<?php

namespace App\Application\Query\Team;

readonly class GetTeamByIdQuery
{
    public function __construct(
        public string $id
    ) {}
}
