<?php

namespace App\Goal\Domain\Repository;

use App\Goal\Infrastructure\Persistence\GoalEntity;

interface GoalRepositoryInterface
{
    /**
     * @return GoalEntity|null
     */
    public function findById(int $id): ?object;

    /**
     * @return list<GoalEntity>
     */
    public function findAll(): array;

    public function save(GoalEntity $entity): void;
}
