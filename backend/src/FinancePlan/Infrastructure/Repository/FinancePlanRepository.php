<?php

namespace App\FinancePlan\Infrastructure\Repository;

use App\FinancePlan\Domain\Repository\FinancePlanRepositoryInterface;
use App\FinancePlan\Infrastructure\Persistence\FinancePlanEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FinancePlanRepository extends ServiceEntityRepository implements FinancePlanRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinancePlanEntity::class);
    }

    /**
     * @return FinancePlanEntity[]
     */
    public function findAllByUserAndPeriod(UserEntity $user, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): array
    {
        return $this->createQueryBuilder('fp')
            ->leftJoin('fp.category', 'fc')->addSelect('fc')
            ->where('fp.deletedAt IS NULL')
            ->andWhere('fp.createdBy = :user')
            ->andWhere('fp.periodStart = :periodStart')
            ->andWhere('fp.periodEnd = :periodEnd')
            ->setParameter('user', $user)
            ->setParameter('periodStart', $periodStart->format('Y-m-d'))
            ->setParameter('periodEnd', $periodEnd->format('Y-m-d'))
            ->orderBy('fp.type', 'ASC')
            ->addOrderBy('fc.title', 'ASC')
            ->addOrderBy('fp.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(FinancePlanEntity $plan): void
    {
        $this->getEntityManager()->persist($plan);
        $this->getEntityManager()->flush();
    }
}
