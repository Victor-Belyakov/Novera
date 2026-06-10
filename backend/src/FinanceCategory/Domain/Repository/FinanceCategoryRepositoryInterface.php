<?php

namespace App\FinanceCategory\Domain\Repository;

use App\FinanceCategory\Infrastructure\Persistence\FinanceCategoryEntity;

interface FinanceCategoryRepositoryInterface
{
    /**
     * @return FinanceCategoryEntity[]
     */
    public function findAll(): array;

    public function findById(int $id): ?FinanceCategoryEntity;

    public function save(FinanceCategoryEntity $category): void;
}
