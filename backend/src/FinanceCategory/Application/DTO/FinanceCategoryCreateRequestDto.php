<?php

namespace App\FinanceCategory\Application\DTO;

final readonly class FinanceCategoryCreateRequestDto
{
    public function __construct(
        public string $title,
    ) {
    }
}
