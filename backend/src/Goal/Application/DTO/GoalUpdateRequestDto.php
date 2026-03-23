<?php

namespace App\Goal\Application\DTO;

final readonly class GoalUpdateRequestDto
{
    public function __construct(
        public ?bool $completed = null,
    ) {
    }
}
