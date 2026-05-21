<?php

namespace App\Enums;

enum CurrencyCode: string
{
    case RUB = 'RUB';
    case USD = 'USD';
    case EUR = 'EUR';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
