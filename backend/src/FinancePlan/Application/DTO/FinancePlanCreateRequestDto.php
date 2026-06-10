<?php

namespace App\FinancePlan\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class FinancePlanCreateRequestDto
{
    public function __construct(
        public string $title,
        public string $type,
        #[SerializedName('planned_amount')]
        public string|int|float $planned_amount,
        #[SerializedName('category_id')]
        public ?int $category_id = null,
        public string $month = '',
    ) {
    }
}
