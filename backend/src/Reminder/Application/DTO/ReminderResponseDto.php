<?php

namespace App\Reminder\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ReminderResponseDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description = null,
        #[SerializedName('entity_type')]
        public ?string $entity_type = null,
        #[SerializedName('entity_id')]
        public ?int $entity_id = null,
        #[SerializedName('notify_at')]
        public string $notify_at,
        public string $frequency,
        #[SerializedName('week_days')]
        /** @var list<int>|null */
        public ?array $week_days = null,
        public string $status,
        #[SerializedName('created_at')]
        public string $created_at,
        #[SerializedName('updated_at')]
        public string $updated_at,
    ) {
    }
}
