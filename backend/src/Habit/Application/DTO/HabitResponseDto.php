<?php

namespace App\Habit\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class HabitResponseDto
{
    public function __construct(
        public int $id,
        public string $title,
        public int $progress,
        /** Всего слотов с результатом (успешные + пропущенные), для отображения "progress / result_total" */
        #[SerializedName('result_total')]
        public int $result_total = 0,
        public string $frequency,
        public ?int $target = null,
        #[SerializedName('progress_percent')]
        public int $progress_percent = 0,
        #[SerializedName('goal_id')]
        public ?int $goal_id = null,
        #[SerializedName('goal_title')]
        public ?string $goal_title = null,
        #[SerializedName('created_at')]
        public string $created_at,
    ) {
    }
}
