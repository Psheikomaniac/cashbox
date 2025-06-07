<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Model\Team\Team;
use App\Domain\Model\Team\TeamUser;
use App\Domain\Model\User\User;
use App\Domain\Repository\TeamUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DoctrineTeamUserRepository implements TeamUserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function findById(string|UuidInterface $id): ?TeamUser
    {
        if (is_string($id)) {
            $id = Uuid::fromString($id);
        }

        return $this->entityManager->getRepository(TeamUser::class)->find($id);
    }

    public function findByTeamAndUser(Team $team, User $user): ?TeamUser
    {
        return $this->entityManager->getRepository(TeamUser::class)
            ->findOneBy([
                'team' => $team,
                'user' => $user
            ]);
    }

    public function findByTeam(Team $team, bool $activeOnly = true, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('tu')
            ->from(TeamUser::class, 'tu')
            ->andWhere('tu.team = :team')
            ->setParameter('team', $team)
            ->orderBy('tu.joinedAt', 'DESC');

        if ($activeOnly) {
            $qb->andWhere('tu.active = :active')
               ->setParameter('active', true);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByUser(User $user, bool $activeOnly = true, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('tu')
            ->from(TeamUser::class, 'tu')
            ->andWhere('tu.user = :user')
            ->setParameter('user', $user)
            ->orderBy('tu.joinedAt', 'DESC');

        if ($activeOnly) {
            $qb->andWhere('tu.active = :active')
               ->setParameter('active', true);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(TeamUser $teamUser): void
    {
        $this->entityManager->persist($teamUser);
        $this->entityManager->flush();
    }

    public function remove(TeamUser $teamUser): void
    {
        $this->entityManager->remove($teamUser);
        $this->entityManager->flush();
    }

    public function countByTeam(Team $team, bool $activeOnly = true): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(tu.id)')
            ->from(TeamUser::class, 'tu')
            ->andWhere('tu.team = :team')
            ->setParameter('team', $team);

        if ($activeOnly) {
            $qb->andWhere('tu.active = :active')
               ->setParameter('active', true);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countByUser(User $user, bool $activeOnly = true): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(tu.id)')
            ->from(TeamUser::class, 'tu')
            ->andWhere('tu.user = :user')
            ->setParameter('user', $user);

        if ($activeOnly) {
            $qb->andWhere('tu.active = :active')
               ->setParameter('active', true);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
