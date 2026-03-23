<?php

namespace App\Category\Domain\Repository;

use App\Category\Infrastructure\Persistence\CategoryEntity;

interface CategoryRepositoryInterface
{
    /**
     * @return CategoryEntity|null
     */
    public function findById(int $id): ?object;

    /**
     * @return list<CategoryEntity>
     */
    public function findAll(): array;

    public function save(CategoryEntity $entity): void;
}
