<?php

namespace App\Telegram\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class TelegramAuthResponseDto
{
    public function __construct(
        public string $token,
        #[SerializedName('refresh_token')]
        public string $refresh_token,
    ) {
    }
}
