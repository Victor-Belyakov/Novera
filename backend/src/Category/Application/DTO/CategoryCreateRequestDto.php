<?php

namespace App\Category\Application\DTO;

final readonly class CategoryCreateRequestDto
{
    public function __construct(
        public string $title,
    ) {
    }
}
