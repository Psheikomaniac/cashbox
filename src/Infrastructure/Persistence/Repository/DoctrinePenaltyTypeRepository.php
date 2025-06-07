<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Model\Penalty\PenaltyType;
use App\Domain\Repository\PenaltyTypeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DoctrinePenaltyTypeRepository implements PenaltyTypeRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function findById(string|UuidInterface $id): ?PenaltyType
    {
        if (is_string($id)) {
            $id = Uuid::fromString($id);
        }

        return $this->entityManager->getRepository(PenaltyType::class)->find($id);
    }

    public function findByName(string $name): ?PenaltyType
    {
        return $this->entityManager->getRepository(PenaltyType::class)
            ->findOneBy(['name' => $name]);
    }

    public function findActive(?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('pt')
            ->from(PenaltyType::class, 'pt')
            ->andWhere('pt.active = :active')
            ->setParameter('active', true)
            ->orderBy('pt.name', 'ASC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function findAll(?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('pt')
            ->from(PenaltyType::class, 'pt')
            ->orderBy('pt.name', 'ASC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(PenaltyType $penaltyType): void
    {
        $this->entityManager->persist($penaltyType);
        $this->entityManager->flush();
    }

    public function remove(PenaltyType $penaltyType): void
    {
        $this->entityManager->remove($penaltyType);
        $this->entityManager->flush();
    }

    public function count(bool $activeOnly = false): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(pt.id)')
            ->from(PenaltyType::class, 'pt');

        if ($activeOnly) {
            $qb->andWhere('pt.active = :active')
               ->setParameter('active', true);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
