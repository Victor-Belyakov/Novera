<?php

namespace App\Task\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class TaskUpdateRequestDto
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        #[SerializedName('assignee_id')]
        public ?int $assignee_id = null,
        #[SerializedName('parent_id')]
        public ?int $parent_id = null,
        #[SerializedName('goal_id')]
        public ?int $goal_id = null,
        public ?string $status = null,
        #[SerializedName('due_date')]
        public ?string $due_date = null,
        public ?string $priority = null,
    ) {
    }
}
