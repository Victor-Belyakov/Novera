<?php

namespace App\Finance\Domain\Enum;

enum FinanceTypeEnum: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::INCOME => 'Доход',
            self::EXPENSE => 'Расход',
        };
    }
}
