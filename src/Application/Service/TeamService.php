<?php

namespace App\Application\Service;

use App\Application\Command\CommandBus;
use App\Application\Command\Team\CreateTeamCommand;
use App\Application\DTO\Team\TeamDTO;
use App\Application\Query\QueryBus;
use App\Application\Query\Team\GetTeamByIdQuery;

/**
 * Service for team-related operations
 */
class TeamService
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus
    ) {}

    /**
     * Creates a new team
     *
     * @return string The ID of the created team
     * @throws \InvalidArgumentException If a team with the same name already exists
     */
    public function createTeam(string $name, string $description = '', bool $active = true): string
    {
        $command = new CreateTeamCommand($name, $description, $active);
        return $this->commandBus->dispatch($command);
    }

    /**
     * Gets a team by its ID
     *
     * @return TeamDTO|null The team DTO or null if not found
     */
    public function getTeamById(string $id): ?TeamDTO
    {
        $query = new GetTeamByIdQuery($id);
        return $this->queryBus->dispatch($query);
    }
}
