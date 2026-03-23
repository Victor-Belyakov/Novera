<?php

namespace App\Reminder\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ReminderCreateRequestDto
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        #[SerializedName('entity_type')]
        public ?string $entity_type = null,
        #[SerializedName('entity_id')]
        public ?int $entity_id = null,
        #[SerializedName('notify_at')]
        public string $notify_at,
        public string $frequency = 'none',
        #[SerializedName('week_days')]
        /** @var list<int>|null */
        public ?array $week_days = null,
    ) {
    }
}
