<?php

namespace App\HealthMetricEntry\Infrastructure\Repository;

use App\HealthMetricEntry\Domain\Repository\HealthMetricEntryRepositoryInterface;
use App\HealthMetricEntry\Infrastructure\Persistence\HealthMetricEntryEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HealthMetricEntryRepository extends ServiceEntityRepository implements HealthMetricEntryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HealthMetricEntryEntity::class);
    }

    /**
     * @return HealthMetricEntryEntity[]
     */
    public function findAllByUser(UserEntity $user): array
    {
        return $this->createQueryBuilder('hme')
            ->leftJoin('hme.metricType', 'hmt')->addSelect('hmt')
            ->where('hme.deletedAt IS NULL')
            ->andWhere('hme.user = :user')
            ->setParameter('user', $user)
            ->orderBy('hme.recordedAt', 'DESC')
            ->addOrderBy('hme.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestByUserAndSlug(UserEntity $user, string $slug): ?HealthMetricEntryEntity
    {
        $result = $this->createQueryBuilder('hme')
            ->leftJoin('hme.metricType', 'hmt')->addSelect('hmt')
            ->where('hme.deletedAt IS NULL')
            ->andWhere('hme.user = :user')
            ->andWhere('hmt.slug = :slug')
            ->setParameter('user', $user)
            ->setParameter('slug', $slug)
            ->orderBy('hme.recordedAt', 'DESC')
            ->addOrderBy('hme.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof HealthMetricEntryEntity ? $result : null;
    }

    public function save(HealthMetricEntryEntity $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}
