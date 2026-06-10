<?php

namespace App\HealthMetricType\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class HealthMetricTypeResponseDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        #[SerializedName('value_kind')]
        public string $value_kind,
        public ?string $unit,
        #[SerializedName('is_active')]
        public bool $is_active,
        #[SerializedName('created_at')]
        public string $created_at,
        #[SerializedName('updated_at')]
        public string $updated_at,
    ) {
    }
}
