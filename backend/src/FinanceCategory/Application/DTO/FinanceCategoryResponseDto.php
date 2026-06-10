<?php

namespace App\FinanceCategory\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class FinanceCategoryResponseDto
{
    public function __construct(
        public int $id,
        public string $title,
        #[SerializedName('created_at')]
        public string $created_at,
        #[SerializedName('updated_at')]
        public string $updated_at,
        #[SerializedName('deleted_at')]
        public ?string $deleted_at = null,
    ) {
    }
}
