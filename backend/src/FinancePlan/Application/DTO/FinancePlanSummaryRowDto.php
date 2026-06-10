<?php

namespace App\FinancePlan\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class FinancePlanSummaryRowDto
{
    public function __construct(
        public string $title,
        public string $type,
        #[SerializedName('type_label')]
        public string $type_label,
        #[SerializedName('category_id')]
        public ?int $category_id,
        #[SerializedName('category_title')]
        public ?string $category_title,
        #[SerializedName('planned_amount')]
        public string $planned_amount,
        #[SerializedName('actual_amount')]
        public string $actual_amount,
        public string $difference,
    ) {
    }
}
