<?php

namespace App\Goal\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class GoalResponseDto
{
    public function __construct(
        public int $id,
        public string $title,
        #[SerializedName('category_id')]
        public ?int $category_id = null,
        #[SerializedName('category_title')]
        public ?string $category_title = null,
        #[SerializedName('due_date')]
        public ?string $due_date = null,
        #[SerializedName('created_by_id')]
        public ?int $created_by_id = null,
        #[SerializedName('created_by_name')]
        public ?string $created_by_name = null,
        #[SerializedName('created_at')]
        public string $created_at,
        #[SerializedName('updated_at')]
        public string $updated_at,
        #[SerializedName('deleted_at')]
        public ?string $deleted_at = null,
        public ?bool $completed = null,
    ) {
    }
}
