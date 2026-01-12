<?php

namespace App\User\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class UserResponseDto
{
    public function __construct(
        public int $id,
        public string $email,
        public string $name,
        #[SerializedName('last_name')]
        public string $last_name,
        #[SerializedName('middle_name')]
        public ?string $middle_name = null,
        #[SerializedName('date_of_birth')]
        public ?string $date_of_birth = null,
    ) {
    }
}

