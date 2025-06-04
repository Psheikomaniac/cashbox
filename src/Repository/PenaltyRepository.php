<?php

namespace App\Repository;

use App\Entity\Penalty;
use App\Entity\PenaltyType;
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

    /**
     * @return Penalty[] Returns an array of unpaid Penalty objects by user
     */
    public function findUnpaidByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.teamUser', 'tu')
            ->andWhere('tu.user = :user')
            ->andWhere('p.paidAt IS NULL')
            ->andWhere('p.archived = :archived')
            ->setParameter('user', $user)
            ->setParameter('archived', false)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns recent Penalty objects
     */
    public function findRecent(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns Penalty objects within date range
     */
    public function findByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.createdAt >= :from')
            ->andWhere('p.createdAt <= :to')
            ->andWhere('p.archived = :archived')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('archived', false)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns Penalty objects by type
     */
    public function findByType(PenaltyType $penaltyType): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.type = :type')
            ->andWhere('p.archived = :archived')
            ->setParameter('type', $penaltyType)
            ->setParameter('archived', false)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns Penalty objects by advanced criteria
     */
    public function findByAdvancedCriteria(
        array $criteria,
        ?string $query = null,
        ?float $minAmount = null,
        ?float $maxAmount = null,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.teamUser', 'tu')
            ->leftJoin('tu.user', 'u')
            ->leftJoin('tu.team', 't')
            ->leftJoin('p.type', 'pt')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false);

        // Apply basic criteria
        foreach ($criteria as $field => $value) {
            if ($value !== null) {
                $qb->andWhere("p.{$field} = :{$field}")
                   ->setParameter($field, $value);
            }
        }

        // Text search in reason, user name, or team name
        if ($query !== null) {
            $qb->andWhere('(
                p.reason LIKE :query OR 
                u.personName.firstName LIKE :query OR 
                u.personName.lastName LIKE :query OR 
                t.name LIKE :query
            )')
            ->setParameter('query', '%' . $query . '%');
        }

        // Amount range
        if ($minAmount !== null) {
            $qb->andWhere('p.amount >= :minAmount')
               ->setParameter('minAmount', $minAmount);
        }
        if ($maxAmount !== null) {
            $qb->andWhere('p.amount <= :maxAmount')
               ->setParameter('maxAmount', $maxAmount);
        }

        // Date range
        if ($startDate !== null) {
            $qb->andWhere('p.createdAt >= :startDate')
               ->setParameter('startDate', $startDate);
        }
        if ($endDate !== null) {
            $qb->andWhere('p.createdAt <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        return $qb->orderBy('p.createdAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Returns statistics for penalties
     */
    public function getStatistics(
        ?Team $team = null,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id) as totalCount, SUM(p.amount) as totalAmount, AVG(p.amount) as averageAmount')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false);

        if ($team !== null) {
            $qb->join('p.teamUser', 'tu')
               ->andWhere('tu.team = :team')
               ->setParameter('team', $team);
        }

        if ($startDate !== null) {
            $qb->andWhere('p.createdAt >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate !== null) {
            $qb->andWhere('p.createdAt <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        $result = $qb->getQuery()->getSingleResult();

        return [
            'totalCount' => (int) ($result['totalCount'] ?? 0),
            'totalAmount' => (float) ($result['totalAmount'] ?? 0),
            'averageAmount' => (float) ($result['averageAmount'] ?? 0),
        ];
    }
}
