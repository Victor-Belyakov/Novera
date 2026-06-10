<?php

namespace App\Finance\Infrastructure\Repository;

use App\Finance\Domain\Repository\FinanceRepositoryInterface;
use App\Finance\Infrastructure\Persistence\FinanceEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FinanceRepository extends ServiceEntityRepository implements FinanceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinanceEntity::class);
    }

    public function findById(int $id): ?FinanceEntity
    {
        $result = parent::find($id);

        return $result instanceof FinanceEntity && !$result->isDeleted()
            ? $result
            : null;
    }

    /**
     * @return FinanceEntity[]
     */
    public function findAllByUser(UserEntity $user): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.category', 'fc')->addSelect('fc')
            ->where('f.deletedAt IS NULL')
            ->andWhere('f.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('f.operationDate', 'DESC')
            ->addOrderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return FinanceEntity[]
     */
    public function findAllByUserAndPeriod(UserEntity $user, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.category', 'fc')->addSelect('fc')
            ->where('f.deletedAt IS NULL')
            ->andWhere('f.createdBy = :user')
            ->andWhere('f.operationDate BETWEEN :periodStart AND :periodEnd')
            ->setParameter('user', $user)
            ->setParameter('periodStart', $periodStart->format('Y-m-d'))
            ->setParameter('periodEnd', $periodEnd->format('Y-m-d'))
            ->orderBy('f.operationDate', 'DESC')
            ->addOrderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(FinanceEntity $finance): void
    {
        $this->getEntityManager()->persist($finance);
        $this->getEntityManager()->flush();
    }
}
