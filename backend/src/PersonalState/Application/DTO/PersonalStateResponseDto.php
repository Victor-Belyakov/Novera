<?php

namespace App\PersonalState\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class PersonalStateResponseDto
{
    public function __construct(
        #[SerializedName('full_name')]
        public string $full_name,
        public int $age,
        #[SerializedName('current_balance')]
        public string $current_balance,
        #[SerializedName('month_income_plan')]
        public string $month_income_plan,
        #[SerializedName('month_income_actual')]
        public string $month_income_actual,
        #[SerializedName('month_expense_plan')]
        public string $month_expense_plan,
        #[SerializedName('month_expense_actual')]
        public string $month_expense_actual,
        #[SerializedName('month_balance_plan')]
        public string $month_balance_plan,
        #[SerializedName('month_balance_actual')]
        public string $month_balance_actual,
        #[SerializedName('active_goals')]
        public int $active_goals,
        #[SerializedName('active_habits')]
        public int $active_habits,
        #[SerializedName('tasks_in_progress')]
        public int $tasks_in_progress,
        #[SerializedName('overdue_tasks')]
        public int $overdue_tasks,
        #[SerializedName('habit_success_rate')]
        public int $habit_success_rate,
        #[SerializedName('last_weight')]
        public ?string $last_weight,
        #[SerializedName('last_weight_recorded_at')]
        public ?string $last_weight_recorded_at,
        #[SerializedName('last_blood_pressure')]
        public ?string $last_blood_pressure,
        #[SerializedName('last_blood_pressure_recorded_at')]
        public ?string $last_blood_pressure_recorded_at,
    ) {
    }
}
