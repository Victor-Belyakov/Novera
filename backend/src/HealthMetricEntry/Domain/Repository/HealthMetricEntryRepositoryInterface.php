<?php

namespace App\HealthMetricEntry\Domain\Repository;

use App\HealthMetricEntry\Infrastructure\Persistence\HealthMetricEntryEntity;
use App\User\Infrastructure\Persistence\UserEntity;

interface HealthMetricEntryRepositoryInterface
{
    /**
     * @return HealthMetricEntryEntity[]
     */
    public function findAllByUser(UserEntity $user): array;

    public function findLatestByUserAndSlug(UserEntity $user, string $slug): ?HealthMetricEntryEntity;

    public function save(HealthMetricEntryEntity $entity): void;
}
