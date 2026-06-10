<?php

namespace App\Finance\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class FinanceCreateRequestDto
{
    public function __construct(
        public string $title,
        public string|int|float $amount,
        public string $type,
        public ?string $description = null,
        #[SerializedName('category_id')]
        public ?int $category_id = null,
        #[SerializedName('operation_date')]
        public string $operation_date = '',
    ) {
    }
}
