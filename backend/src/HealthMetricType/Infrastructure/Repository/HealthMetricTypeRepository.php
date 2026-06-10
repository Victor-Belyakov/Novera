<?php

namespace App\HealthMetricType\Infrastructure\Repository;

use App\HealthMetricType\Domain\Repository\HealthMetricTypeRepositoryInterface;
use App\HealthMetricType\Infrastructure\Persistence\HealthMetricTypeEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HealthMetricTypeRepository extends ServiceEntityRepository implements HealthMetricTypeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HealthMetricTypeEntity::class);
    }

    /**
     * @return HealthMetricTypeEntity[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('hmt')
            ->where('hmt.deletedAt IS NULL')
            ->orderBy('hmt.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return HealthMetricTypeEntity[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('hmt')
            ->where('hmt.deletedAt IS NULL')
            ->andWhere('hmt.isActive = true')
            ->orderBy('hmt.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?HealthMetricTypeEntity
    {
        $result = parent::find($id);
        return $result instanceof HealthMetricTypeEntity && !$result->isDeleted() ? $result : null;
    }

    public function findBySlug(string $slug): ?HealthMetricTypeEntity
    {
        $result = $this->findOneBy(['slug' => $slug, 'deletedAt' => null]);
        return $result instanceof HealthMetricTypeEntity ? $result : null;
    }

    public function save(HealthMetricTypeEntity $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}
