<?php

namespace App\Category\Domain\Service;

use App\Category\Application\DTO\CategoryCreateRequestDto;
use App\Category\Application\DTO\CategoryResponseDto;

interface CategoryServiceInterface
{
    /**
     * @return list<CategoryResponseDto>
     */
    public function getList(): array;

    public function create(CategoryCreateRequestDto $dto): CategoryResponseDto;
}
