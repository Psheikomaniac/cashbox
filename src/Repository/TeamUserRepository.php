<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamUser>
 *
 * @method TeamUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamUser[]    findAll()
 * @method TeamUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamUser::class);
    }

    /**
     * @return TeamUser[] Returns an array of active TeamUser objects
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('tu')
            ->andWhere('tu.active = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TeamUser[] Returns an array of TeamUser objects by team
     */
    public function findByTeam(Team $team): array
    {
        return $this->createQueryBuilder('tu')
            ->andWhere('tu.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TeamUser[] Returns an array of TeamUser objects by user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('tu')
            ->andWhere('tu.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TeamUser|null Returns a TeamUser object by team and user
     */
    public function findOneByTeamAndUser(Team $team, User $user): ?TeamUser
    {
        return $this->createQueryBuilder('tu')
            ->andWhere('tu.team = :team')
            ->andWhere('tu.user = :user')
            ->setParameter('team', $team)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
