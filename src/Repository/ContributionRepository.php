<?php

namespace App\Repository;

use App\Entity\Contribution;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contribution>
 *
 * @method Contribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contribution[]    findAll()
 * @method Contribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contribution::class);
    }

    /**
     * @return Contribution[] Returns an array of Contribution objects by team
     */
    public function findByTeam(Team $team): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.teamUser', 'tu')
            ->andWhere('tu.team = :team')
            ->setParameter('team', $team)
            ->orderBy('c.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Contribution[] Returns an array of Contribution objects by user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.teamUser', 'tu')
            ->andWhere('tu.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Contribution[] Returns an array of Contribution objects by team user
     */
    public function findByTeamUser(TeamUser $teamUser): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.teamUser = :teamUser')
            ->setParameter('teamUser', $teamUser)
            ->orderBy('c.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Contribution[] Returns an array of unpaid Contribution objects
     */
    public function findUnpaid(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.paidAt IS NULL')
            ->andWhere('c.active = :active')
            ->setParameter('active', true)
            ->orderBy('c.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Contribution[] Returns an array of upcoming Contribution objects
     */
    public function findUpcoming(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.dueDate > :date')
            ->andWhere('c.paidAt IS NULL')
            ->andWhere('c.active = :active')
            ->setParameter('date', $date)
            ->setParameter('active', true)
            ->orderBy('c.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
