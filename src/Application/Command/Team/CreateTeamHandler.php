<?php

namespace App\Application\Command\Team;

use App\Domain\Model\Team\Team;
use App\Domain\Repository\TeamRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateTeamHandler
{
    public function __construct(
        private readonly TeamRepositoryInterface $teamRepository
    ) {}

    /**
     * Handles the CreateTeamCommand and returns the ID of the created team
     */
    public function __invoke(CreateTeamCommand $command): string
    {
        // Check if a team with the same name already exists
        $existingTeam = $this->teamRepository->findByName($command->name);
        if ($existingTeam !== null) {
            throw new \InvalidArgumentException(sprintf('Team with name "%s" already exists', $command->name));
        }

        // Create a new team
        $team = new Team(
            $command->name,
            $command->description,
            $command->active
        );

        // Save the team
        $this->teamRepository->save($team);

        // Return the team ID
        return $team->getId()->toString();
    }
}
