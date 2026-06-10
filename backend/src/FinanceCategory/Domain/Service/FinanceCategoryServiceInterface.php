<?php

namespace App\FinanceCategory\Domain\Service;

use App\FinanceCategory\Application\DTO\FinanceCategoryCreateRequestDto;
use App\FinanceCategory\Application\DTO\FinanceCategoryResponseDto;

interface FinanceCategoryServiceInterface
{
    /**
     * @return FinanceCategoryResponseDto[]
     */
    public function getList(): array;

    public function create(FinanceCategoryCreateRequestDto $dto): FinanceCategoryResponseDto;
}
