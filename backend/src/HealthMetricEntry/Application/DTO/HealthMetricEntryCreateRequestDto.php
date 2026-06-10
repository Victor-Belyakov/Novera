<?php

namespace App\HealthMetricEntry\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class HealthMetricEntryCreateRequestDto
{
    public function __construct(
        #[SerializedName('metric_type_id')]
        public int $metric_type_id,
        #[SerializedName('recorded_at')]
        public string $recorded_at = '',
        #[SerializedName('value_number')]
        public string|int|float|null $value_number = null,
        #[SerializedName('value_text')]
        public ?string $value_text = null,
        public ?string $note = null,
    ) {
    }
}
