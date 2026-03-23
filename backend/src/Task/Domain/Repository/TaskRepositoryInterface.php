<?php

namespace App\Task\Domain\Repository;

use App\Task\Infrastructure\Persistence\TaskEntity;

interface TaskRepositoryInterface
{
    public function findById(int $id): ?TaskEntity;

    /**
     * @return TaskEntity[]
     */
    public function findAllNotDeleted(): array;

    public function save(TaskEntity $task): void;

    public function remove(TaskEntity $task): void;
}
