<?php

namespace App\Repository;

use App\Entity\Penalty;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Penalty>
 *
 * @method Penalty|null find($id, $lockMode = null, $lockVersion = null)
 * @method Penalty|null findOneBy(array $criteria, array $orderBy = null)
 * @method Penalty[]    findAll()
 * @method Penalty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PenaltyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Penalty::class);
    }

    /**
     * @return Penalty[] Returns an array of unpaid Penalty objects
     */
    public function findUnpaid(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.paidAt IS NULL')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns an array of Penalty objects by team
     */
    public function findByTeam(Team $team): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.teamUser', 'tu')
            ->andWhere('tu.team = :team')
            ->setParameter('team', $team)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns an array of Penalty objects by user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.teamUser', 'tu')
            ->andWhere('tu.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns an array of Penalty objects by team user
     */
    public function findByTeamUser(TeamUser $teamUser): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.teamUser = :teamUser')
            ->setParameter('teamUser', $teamUser)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
