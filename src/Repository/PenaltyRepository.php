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
     * Creates a base QueryBuilder for Penalties
     */
    private function createPenaltyQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC');
    }

    /**
     * @return Penalty[] Returns an array of unpaid Penalty objects
     */
    public function findUnpaid(): array
    {
        return $this->createPenaltyQueryBuilder()
            ->andWhere('p.paidAt IS NULL')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns an array of Penalty objects by team
     */
    public function findByTeam(Team $team): array
    {
        return $this->createPenaltyQueryBuilder()
            ->join('p.teamUser', 'tu')
            ->andWhere('tu.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns an array of Penalty objects by user
     */
    public function findByUser(User $user): array
    {
        return $this->createPenaltyQueryBuilder()
            ->join('p.teamUser', 'tu')
            ->andWhere('tu.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns an array of Penalty objects by team user
     */
    public function findByTeamUser(TeamUser $teamUser): array
    {
        return $this->createPenaltyQueryBuilder()
            ->andWhere('p.teamUser = :teamUser')
            ->setParameter('teamUser', $teamUser)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns an array of unpaid Penalty objects by user
     */
    public function findUnpaidByUser(User $user): array
    {
        return $this->createPenaltyQueryBuilder()
            ->join('p.teamUser', 'tu')
            ->andWhere('tu.user = :user')
            ->andWhere('p.paidAt IS NULL')
            ->andWhere('p.archived = :archived')
            ->setParameter('user', $user)
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns recent Penalty objects
     */
    public function findRecent(int $limit): array
    {
        return $this->createPenaltyQueryBuilder()
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns Penalty objects within date range
     */
    public function findByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createPenaltyQueryBuilder()
            ->andWhere('p.createdAt >= :from')
            ->andWhere('p.createdAt <= :to')
            ->andWhere('p.archived = :archived')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Penalty[] Returns Penalty objects by type
     */
    public function findByType(PenaltyType $penaltyType): array
    {
        return $this->createPenaltyQueryBuilder()
            ->andWhere('p.type = :type')
            ->andWhere('p.archived = :archived')
            ->setParameter('type', $penaltyType)
            ->setParameter('archived', false)
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
        $qb = $this->createPenaltyQueryBuilder()
            ->leftJoin('p.teamUser', 'tu')
            ->leftJoin('tu.user', 'u')
            ->leftJoin('tu.team', 't')
            ->leftJoin('p.type', 'pt')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false);

        // Apply basic criteria - only allow specific fields for security
        $allowedFields = ['reason', 'paidAt'];
        foreach ($criteria as $field => $value) {
            if ($value !== null && in_array($field, $allowedFields, true)) {
                $paramName = 'param_' . $field;
                $qb->andWhere("p.{$field} = :{$paramName}")
                   ->setParameter($paramName, $value);
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

        // Amount range - updated to work with Money value object
        if ($minAmount !== null) {
            $qb->andWhere('p.money.amount >= :minAmount')
               ->setParameter('minAmount', (int)($minAmount * 100)); // Convert to cents
        }
        if ($maxAmount !== null) {
            $qb->andWhere('p.money.amount <= :maxAmount')
               ->setParameter('maxAmount', (int)($maxAmount * 100)); // Convert to cents
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

        return $qb->getQuery()
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
        // We need to create a new QueryBuilder here because we're using SELECT
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id) as totalCount, SUM(p.money.amount) as totalAmount, AVG(p.money.amount) as averageAmount')
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
            'totalAmount' => (float) ($result['totalAmount'] ?? 0) / 100, // Convert from cents to dollars/euros
            'averageAmount' => (float) ($result['averageAmount'] ?? 0) / 100, // Convert from cents to dollars/euros
        ];
    }

    /**
     * @return Penalty[] Returns paginated penalties with filters
     */
    public function findPaginated(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $qb = $this->createPenaltyQueryBuilder()
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false);

        if (isset($filters['user'])) {
            $qb->leftJoin('p.teamUser', 'tu')
               ->leftJoin('tu.user', 'u')
               ->andWhere('u.id = :user')
               ->setParameter('user', $filters['user']);
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'paid') {
                $qb->andWhere('p.paidAt IS NOT NULL');
            } elseif ($filters['status'] === 'unpaid') {
                $qb->andWhere('p.paidAt IS NULL');
            }
        }

        return $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count penalties with filters
     */
    public function countFiltered(array $filters = []): int
    {
        // We need to create a new QueryBuilder here because we're using SELECT
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false);

        if (isset($filters['user'])) {
            $qb->leftJoin('p.teamUser', 'tu')
               ->leftJoin('tu.user', 'u')
               ->andWhere('u.id = :user')
               ->setParameter('user', $filters['user']);
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'paid') {
                $qb->andWhere('p.paidAt IS NOT NULL');
            } elseif ($filters['status'] === 'unpaid') {
                $qb->andWhere('p.paidAt IS NULL');
            }
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
