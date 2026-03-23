<?php

namespace App\Goal\Infrastructure\Repository;

use App\Goal\Domain\Repository\GoalRepositoryInterface;
use App\Goal\Infrastructure\Persistence\GoalEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class GoalRepository implements GoalRepositoryInterface
{
    private EntityRepository $repository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(GoalEntity::class);
    }

    public function findById(int $id): ?object
    {
        $entity = $this->repository->find($id);
        if ($entity instanceof GoalEntity && $entity->isDeleted()) {
            return null;
        }
        return $entity;
    }

    public function findAll(): array
    {
        return $this->repository->createQueryBuilder('g')
            ->leftJoin('g.category', 'c')->addSelect('c')
            ->leftJoin('g.createdBy', 'u')->addSelect('u')
            ->where('g.deletedAt IS NULL')
            ->orderBy('g.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(GoalEntity $entity): void
    {
        $this->em->persist($entity);
        $this->em->flush();
    }
}
