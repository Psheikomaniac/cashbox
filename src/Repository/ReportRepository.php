<?php

namespace App\Repository;

use App\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Report>
 *
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * @return Report[] Returns an array of scheduled Report objects
     */
    public function findScheduled(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.scheduled = :scheduled')
            ->setParameter('scheduled', true)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Report[] Returns an array of Report objects by type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.type = :type')
            ->setParameter('type', $type)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Report[] Returns paginated reports with filters
     */
    public function findPaginated(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('r');

        if (isset($filters['type'])) {
            $qb->andWhere('r.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'generated') {
                $qb->andWhere('r.generatedAt IS NOT NULL');
            } elseif ($filters['status'] === 'pending') {
                $qb->andWhere('r.generatedAt IS NULL');
            }
        }

        return $qb->orderBy('r.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count reports with filters
     */
    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)');

        if (isset($filters['type'])) {
            $qb->andWhere('r.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'generated') {
                $qb->andWhere('r.generatedAt IS NOT NULL');
            } elseif ($filters['status'] === 'pending') {
                $qb->andWhere('r.generatedAt IS NULL');
            }
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
