<?php

namespace App\Repository;

use App\Entity\NotificationPreference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationPreference>
 *
 * @method NotificationPreference|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationPreference|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationPreference[]    findAll()
 * @method NotificationPreference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationPreference::class);
    }

    /**
     * @return NotificationPreference[] Returns an array of NotificationPreference objects for a user
     */
    public function findByUser(string $userId): array
    {
        return $this->createQueryBuilder('np')
            ->andWhere('np.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('np.notificationType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return NotificationPreference|null Returns a NotificationPreference object for a user and notification type
     */
    public function findOneByUserAndType(string $userId, string $notificationType): ?NotificationPreference
    {
        return $this->createQueryBuilder('np')
            ->andWhere('np.user = :userId')
            ->andWhere('np.notificationType = :notificationType')
            ->setParameter('userId', $userId)
            ->setParameter('notificationType', $notificationType)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
