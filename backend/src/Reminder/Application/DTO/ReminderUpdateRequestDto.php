<?php

namespace App\Reminder\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ReminderUpdateRequestDto
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        #[SerializedName('notify_at')]
        public ?string $notify_at = null,
        public ?string $frequency = null,
        #[SerializedName('week_days')]
        /** @var list<int>|null */
        public ?array $week_days = null,
        public ?string $status = null,
    ) {
    }
}
