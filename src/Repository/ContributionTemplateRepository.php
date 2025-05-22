<?php

namespace App\Repository;

use App\Entity\ContributionTemplate;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContributionTemplate>
 *
 * @method ContributionTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContributionTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContributionTemplate[]    findAll()
 * @method ContributionTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributionTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContributionTemplate::class);
    }

    /**
     * @return ContributionTemplate[] Returns an array of active ContributionTemplate objects
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
     * @return ContributionTemplate[] Returns an array of ContributionTemplate objects by team
     */
    public function findByTeam(Team $team): array
    {
        return $this->createQueryBuilder('ct')
            ->andWhere('ct.team = :team')
            ->andWhere('ct.active = :active')
            ->setParameter('team', $team)
            ->setParameter('active', true)
            ->orderBy('ct.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ContributionTemplate[] Returns an array of recurring ContributionTemplate objects
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
