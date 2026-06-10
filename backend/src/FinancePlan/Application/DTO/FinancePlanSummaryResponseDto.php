<?php

namespace App\FinancePlan\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class FinancePlanSummaryResponseDto
{
    /**
     * @param FinancePlanSummaryRowDto[] $rows
     */
    public function __construct(
        public string $month,
        #[SerializedName('planned_income')]
        public string $planned_income,
        #[SerializedName('actual_income')]
        public string $actual_income,
        #[SerializedName('planned_expense')]
        public string $planned_expense,
        #[SerializedName('actual_expense')]
        public string $actual_expense,
        #[SerializedName('balance_plan')]
        public string $balance_plan,
        #[SerializedName('balance_actual')]
        public string $balance_actual,
        public array $rows,
    ) {
    }
}
