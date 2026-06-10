<?php

namespace App\FinancePlan\Domain\Repository;

use App\FinancePlan\Infrastructure\Persistence\FinancePlanEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

interface FinancePlanRepositoryInterface
{
    /**
     * @return FinancePlanEntity[]
     */
    public function findAllByUserAndPeriod(UserEntity $user, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): array;

    public function save(FinancePlanEntity $plan): void;
}
