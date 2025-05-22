<?php

namespace App\Repository;

use App\Entity\ContributionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContributionType>
 *
 * @method ContributionType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContributionType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContributionType[]    findAll()
 * @method ContributionType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributionTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContributionType::class);
    }

    /**
     * @return ContributionType[] Returns an array of active ContributionType objects
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('ct')
            ->andWhere('ct.active = :active')
            ->setParameter('active', true)
            ->orderBy('ct.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ContributionType[] Returns an array of recurring ContributionType objects
     */
    public function findRecurring(): array
    {
        return $this->createQueryBuilder('ct')
            ->andWhere('ct.recurring = :recurring')
            ->andWhere('ct.active = :active')
            ->setParameter('recurring', true)
            ->setParameter('active', true)
            ->orderBy('ct.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
