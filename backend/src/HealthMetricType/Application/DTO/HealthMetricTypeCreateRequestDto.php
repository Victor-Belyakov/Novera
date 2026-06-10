<?php

namespace App\HealthMetricType\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class HealthMetricTypeCreateRequestDto
{
    public function __construct(
        public string $title,
        public string $slug,
        #[SerializedName('value_kind')]
        public string $value_kind = 'number',
        public ?string $unit = null,
    ) {
    }
}
