<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Model\Team\Team;
use App\Domain\Repository\TeamRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DoctrineTeamRepository implements TeamRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function findById(string|UuidInterface $id): ?Team
    {
        if (is_string($id)) {
            $id = Uuid::fromString($id);
        }

        return $this->entityManager->getRepository(Team::class)->find($id);
    }

    public function findByName(string $name): ?Team
    {
        return $this->entityManager->getRepository(Team::class)
            ->findOneBy(['name' => $name]);
    }

    public function findActive(?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Team::class, 't')
            ->andWhere('t.active = :active')
            ->setParameter('active', true)
            ->orderBy('t.name', 'ASC');

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
            ->select('t')
            ->from(Team::class, 't')
            ->orderBy('t.name', 'ASC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(Team $team): void
    {
        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }

    public function remove(Team $team): void
    {
        $this->entityManager->remove($team);
        $this->entityManager->flush();
    }

    public function count(bool $activeOnly = false): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(t.id)')
            ->from(Team::class, 't');

        if ($activeOnly) {
            $qb->andWhere('t.active = :active')
               ->setParameter('active', true);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
