<?php

namespace App\Category\Infrastructure\Repository;

use App\Category\Domain\Repository\CategoryRepositoryInterface;
use App\Category\Infrastructure\Persistence\CategoryEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class CategoryRepository implements CategoryRepositoryInterface
{
    private EntityRepository $repository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(CategoryEntity::class);
    }

    public function findById(int $id): ?object
    {
        $entity = $this->repository->find($id);
        if ($entity instanceof CategoryEntity && $entity->isDeleted()) {
            return null;
        }
        return $entity;
    }

    public function findAll(): array
    {
        return $this->repository->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(CategoryEntity $entity): void
    {
        $this->em->persist($entity);
        $this->em->flush();
    }
}
