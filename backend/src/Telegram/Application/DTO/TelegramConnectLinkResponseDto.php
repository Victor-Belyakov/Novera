<?php

namespace App\Telegram\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class TelegramConnectLinkResponseDto
{
    public function __construct(
        public string $url,
        #[SerializedName('expires_at')]
        public string $expires_at,
    ) {
    }
}
