<?php

namespace App\FinancePlan\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class FinancePlanResponseDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $type,
        #[SerializedName('type_label')]
        public string $type_label,
        #[SerializedName('planned_amount')]
        public string $planned_amount,
        #[SerializedName('category_id')]
        public ?int $category_id,
        #[SerializedName('category_title')]
        public ?string $category_title,
        public string $month,
        #[SerializedName('period_start')]
        public string $period_start,
        #[SerializedName('period_end')]
        public string $period_end,
        #[SerializedName('created_at')]
        public string $created_at,
        #[SerializedName('updated_at')]
        public string $updated_at,
    ) {
    }
}
