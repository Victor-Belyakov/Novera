<?php

namespace App\User\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class UserUpdateRequestDto
{
    public function __construct(
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $name = null,
        #[SerializedName('last_name')]
        public ?string $last_name = null,
        #[SerializedName('middle_name')]
        public ?string $middle_name = null,
        #[SerializedName('date_of_birth')]
        public ?string $date_of_birth = null,
        #[SerializedName('telegram_id')]
        public ?string $telegram_id = null,
    ) {
    }
}
