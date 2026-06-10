<?php

namespace App\Goal\Domain\Repository;

use App\Goal\Infrastructure\Persistence\GoalEntity;
use App\User\Infrastructure\Persistence\UserEntity;

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

    /**
     * @return list<GoalEntity>
     */
    public function findAllByUser(UserEntity $user): array;

    public function save(GoalEntity $entity): void;
}
