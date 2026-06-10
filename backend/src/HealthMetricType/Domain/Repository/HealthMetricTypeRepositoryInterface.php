<?php

namespace App\HealthMetricType\Domain\Repository;

use App\HealthMetricType\Infrastructure\Persistence\HealthMetricTypeEntity;

interface HealthMetricTypeRepositoryInterface
{
    /**
     * @return HealthMetricTypeEntity[]
     */
    public function findAll(): array;

    /**
     * @return HealthMetricTypeEntity[]
     */
    public function findAllActive(): array;

    public function findById(int $id): ?HealthMetricTypeEntity;

    public function findBySlug(string $slug): ?HealthMetricTypeEntity;

    public function save(HealthMetricTypeEntity $entity): void;
}
