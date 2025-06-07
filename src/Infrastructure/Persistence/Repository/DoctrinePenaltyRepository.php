<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Model\Penalty\Penalty;
use App\Domain\Repository\PenaltyRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DoctrinePenaltyRepository implements PenaltyRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function findById(string|UuidInterface $id): ?Penalty
    {
        if (is_string($id)) {
            $id = Uuid::fromString($id);
        }

        return $this->entityManager->getRepository(Penalty::class)->find($id);
    }

    public function findUnpaid(?string $teamId = null, ?string $userId = null, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Penalty::class, 'p')
            ->andWhere('p.paidAt IS NULL')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false)
            ->orderBy('p.createdAt', 'DESC');

        if ($teamId) {
            $qb->join('p.teamUser', 'tu')
               ->andWhere('tu.team = :teamId')
               ->setParameter('teamId', Uuid::fromString($teamId));
        }

        if ($userId) {
            $qb->join('p.teamUser', 'tu')
               ->join('tu.user', 'u')
               ->andWhere('u.id = :userId')
               ->setParameter('userId', Uuid::fromString($userId));
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(Penalty $penalty): void
    {
        $this->entityManager->persist($penalty);
        $this->entityManager->flush();

        // Dispatch domain events
        foreach ($penalty->releaseEvents() as $event) {
            // In a real implementation, you would dispatch these events
            // using Symfony's event dispatcher or message bus
        }
    }

    public function remove(Penalty $penalty): void
    {
        $this->entityManager->remove($penalty);
        $this->entityManager->flush();
    }

    public function countUnpaid(?string $teamId = null, ?string $userId = null): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Penalty::class, 'p')
            ->andWhere('p.paidAt IS NULL')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false);

        if ($teamId) {
            $qb->join('p.teamUser', 'tu')
               ->andWhere('tu.team = :teamId')
               ->setParameter('teamId', Uuid::fromString($teamId));
        }

        if ($userId) {
            $qb->join('p.teamUser', 'tu')
               ->join('tu.user', 'u')
               ->andWhere('u.id = :userId')
               ->setParameter('userId', Uuid::fromString($userId));
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
