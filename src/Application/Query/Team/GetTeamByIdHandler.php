<?php

namespace App\Application\Query\Team;

use App\Application\DTO\Team\TeamDTO;
use App\Domain\Repository\TeamRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetTeamByIdHandler
{
    public function __construct(
        private readonly TeamRepositoryInterface $teamRepository
    ) {}

    /**
     * Handles the GetTeamByIdQuery and returns a TeamDTO or null if not found
     */
    public function __invoke(GetTeamByIdQuery $query): ?TeamDTO
    {
        $team = $this->teamRepository->findById($query->id);

        if ($team === null) {
            return null;
        }

        return TeamDTO::fromEntity($team);
    }
}
