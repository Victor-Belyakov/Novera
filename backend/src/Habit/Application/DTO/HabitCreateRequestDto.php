<?php

namespace App\Habit\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class HabitCreateRequestDto
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        #[SerializedName('category_id')]
        public ?int $category_id = null,
        public string $frequency = 'daily',
        #[SerializedName('preferred_time')]
        public ?string $preferred_time = null,
        #[SerializedName('goal_id')]
        public ?int $goal_id = null,
        /** Цель по количеству выполнений за период (для progress_percent). Например 7 для weekly */
        public ?int $target = null,
    ) {
    }
}
