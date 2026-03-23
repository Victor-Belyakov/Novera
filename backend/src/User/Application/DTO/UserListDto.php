<?php

namespace App\User\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class UserListDto
{
    public function __construct(
        public int $id,
        public string $fio,
        public string $email,
        public ?string $phone,
        #[SerializedName('created_at')]
        public string $created_at,
        #[SerializedName('telegram_id')]
        public ?string $telegram_id = null,
    ) {
    }
}
