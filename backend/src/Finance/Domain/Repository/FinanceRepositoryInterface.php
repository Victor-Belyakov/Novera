<?php

namespace App\Finance\Domain\Repository;

use App\Finance\Infrastructure\Persistence\FinanceEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

interface FinanceRepositoryInterface
{
    public function findById(int $id): ?FinanceEntity;

    /**
     * @return FinanceEntity[]
     */
    public function findAllByUser(UserEntity $user): array;

    /**
     * @return FinanceEntity[]
     */
    public function findAllByUserAndPeriod(UserEntity $user, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): array;

    public function save(FinanceEntity $finance): void;
}
