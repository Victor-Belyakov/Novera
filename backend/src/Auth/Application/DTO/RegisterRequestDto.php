<?php

namespace App\Auth\Application\DTO;

use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class RegisterRequestDto
{
    public function __construct(
        public string  $email,
        public string  $password,
        public string  $name,
        #[SerializedName('last_name')]
        public string  $last_name,
        #[SerializedName('middle_name')]
        public ?string $middle_name = null,
        #[SerializedName('date_birth')]
        public ?DateTimeImmutable $date_birth = null,
    ) {
    }
}
