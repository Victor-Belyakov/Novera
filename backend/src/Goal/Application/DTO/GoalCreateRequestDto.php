<?php

namespace App\Goal\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class GoalCreateRequestDto
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        #[SerializedName('category_id')]
        public ?int $category_id = null,
        public string $type = 'result',
        #[SerializedName('due_date')]
        public string $due_date = '',
        public int $priority = 0,
    ) {
    }
}
