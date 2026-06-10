<?php

namespace App\Task\Domain\Repository;

use App\Task\Infrastructure\Persistence\TaskEntity;
use App\User\Infrastructure\Persistence\UserEntity;

interface TaskRepositoryInterface
{
    public function findById(int $id): ?TaskEntity;

    /**
     * @return TaskEntity[]
     */
    public function findAllNotDeleted(): array;

    /**
     * @return TaskEntity[]
     */
    public function findAllByUser(UserEntity $user): array;

    public function save(TaskEntity $task): void;

    public function remove(TaskEntity $task): void;
}
