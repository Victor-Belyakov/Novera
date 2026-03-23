<?php

namespace App\Task\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class TaskResponseDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description = null,
        #[SerializedName('assignee_id')]
        public ?int $assignee_id = null,
        #[SerializedName('assignee_name')]
        public ?string $assignee_name = null,
        #[SerializedName('created_by_id')]
        public ?int $created_by_id = null,
        #[SerializedName('created_by_name')]
        public ?string $created_by_name = null,
        #[SerializedName('parent_id')]
        public ?int $parent_id = null,
        #[SerializedName('goal_id')]
        public ?int $goal_id = null,
        #[SerializedName('goal_title')]
        public ?string $goal_title = null,
        public string $status,
        #[SerializedName('due_date')]
        public ?string $due_date = null,
        public string $priority,
        #[SerializedName('created_at')]
        public string $created_at,
        #[SerializedName('updated_at')]
        public string $updated_at,
        #[SerializedName('deleted_at')]
        public ?string $deleted_at = null,
    ) {
    }
}
