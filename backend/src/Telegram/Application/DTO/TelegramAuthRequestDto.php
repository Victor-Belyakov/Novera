<?php

namespace App\Telegram\Application\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class TelegramAuthRequestDto
{
    public function __construct(
        #[SerializedName('init_data')]
        public string $init_data,
    ) {
    }
}
