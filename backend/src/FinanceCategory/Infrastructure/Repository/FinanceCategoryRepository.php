<?php

namespace App\FinanceCategory\Infrastructure\Repository;

use App\FinanceCategory\Domain\Repository\FinanceCategoryRepositoryInterface;
use App\FinanceCategory\Infrastructure\Persistence\FinanceCategoryEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FinanceCategoryRepository extends ServiceEntityRepository implements FinanceCategoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinanceCategoryEntity::class);
    }

    /**
     * @return FinanceCategoryEntity[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('fc')
            ->where('fc.deletedAt IS NULL')
            ->orderBy('fc.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?FinanceCategoryEntity
    {
        $result = parent::find($id);

        return $result instanceof FinanceCategoryEntity && !$result->isDeleted()
            ? $result
            : null;
    }

    public function save(FinanceCategoryEntity $category): void
    {
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
    }
}
