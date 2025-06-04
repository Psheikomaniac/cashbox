<?php

namespace App\Repository;

use App\Entity\Contribution;
use App\Entity\ContributionPayment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContributionPayment>
 *
 * @method ContributionPayment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContributionPayment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContributionPayment[]    findAll()
 * @method ContributionPayment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributionPaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContributionPayment::class);
    }

    /**
     * @return ContributionPayment[] Returns an array of ContributionPayment objects by contribution
     */
    public function findByContribution(Contribution $contribution): array
    {
        return $this->createQueryBuilder('cp')
            ->andWhere('cp.contribution = :contribution')
            ->setParameter('contribution', $contribution)
            ->orderBy('cp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the total amount paid for a contribution
     */
    public function getTotalPaidAmount(Contribution $contribution): \App\ValueObject\Money
    {
        $result = $this->createQueryBuilder('cp')
            ->select('SUM(cp.amountCents) as total')
            ->andWhere('cp.contribution = :contribution')
            ->setParameter('contribution', $contribution)
            ->getQuery()
            ->getSingleScalarResult();

        $totalCents = (int) ($result ?? 0);
        
        // Get currency from contribution (all payments must match contribution currency)
        return new \App\ValueObject\Money($totalCents, $contribution->getAmount()->getCurrency());
    }
}
