<?php

namespace App\Finance\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class FinanceUpdateRequestDto
{
    public function __construct(
        public ?string $title = null,
        public string|int|float|null $amount = null,
        public ?string $type = null,
        public ?string $description = null,
        #[SerializedName('category_id')]
        public ?int $category_id = null,
        #[SerializedName('operation_date')]
        public ?string $operation_date = null,
    ) {
    }
}
