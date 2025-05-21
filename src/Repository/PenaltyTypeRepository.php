<?php

namespace App\Repository;

use App\Entity\PenaltyType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PenaltyType>
 *
 * @method PenaltyType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PenaltyType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PenaltyType[]    findAll()
 * @method PenaltyType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PenaltyTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PenaltyType::class);
    }

    /**
     * @return PenaltyType[] Returns an array of active PenaltyType objects
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.active = :active')
            ->setParameter('active', true)
            ->orderBy('pt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return PenaltyType[] Returns an array of drink PenaltyType objects
     */
    public function findDrinks(): array
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.type = :type')
            ->setParameter('type', 'drink')
            ->andWhere('pt.active = :active')
            ->setParameter('active', true)
            ->orderBy('pt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
