<?php

namespace App\Finance\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class FinanceResponseDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public string $amount,
        public string $type,
        #[SerializedName('type_label')]
        public string $type_label,
        #[SerializedName('category_id')]
        public ?int $category_id,
        #[SerializedName('category_title')]
        public ?string $category_title,
        #[SerializedName('operation_date')]
        public string $operation_date,
        #[SerializedName('created_by_id')]
        public int $created_by_id,
        #[SerializedName('created_by_name')]
        public string $created_by_name,
        #[SerializedName('created_at')]
        public string $created_at,
        #[SerializedName('updated_at')]
        public string $updated_at,
        #[SerializedName('deleted_at')]
        public ?string $deleted_at = null,
    ) {
    }
}
