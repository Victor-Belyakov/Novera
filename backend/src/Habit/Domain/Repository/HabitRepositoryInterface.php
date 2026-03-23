<?php

namespace App\Habit\Domain\Repository;

use App\Habit\Infrastructure\Persistence\HabitEntity;
use App\User\Infrastructure\Persistence\UserEntity;

interface HabitRepositoryInterface
{
    public function findById(int $id): ?HabitEntity;

    public function findByIdWithLogs(int $id): ?HabitEntity;

    /**
     * @return list<HabitEntity>
     */
    public function findAllByUser(UserEntity $user): array;

    public function save(HabitEntity $entity): void;
}
