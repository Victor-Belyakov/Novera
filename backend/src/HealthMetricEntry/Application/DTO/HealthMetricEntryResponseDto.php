<?php

namespace App\HealthMetricEntry\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class HealthMetricEntryResponseDto
{
    public function __construct(
        public int $id,
        #[SerializedName('metric_type_id')]
        public int $metric_type_id,
        #[SerializedName('metric_type_title')]
        public string $metric_type_title,
        #[SerializedName('metric_type_slug')]
        public string $metric_type_slug,
        #[SerializedName('value_kind')]
        public string $value_kind,
        public ?string $unit,
        #[SerializedName('recorded_at')]
        public string $recorded_at,
        #[SerializedName('value_number')]
        public ?string $value_number,
        #[SerializedName('value_text')]
        public ?string $value_text,
        public ?string $note,
        #[SerializedName('created_at')]
        public string $created_at,
    ) {
    }
}
