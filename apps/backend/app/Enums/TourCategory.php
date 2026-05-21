<?php

namespace App\Enums;

enum TourCategory: string
{
    case HIKING = 'hiking';
    case CITY = 'city';
    case GASTRO = 'gastro';
    case NATURE = 'nature';
    case WINTER = 'winter';
    case ADVENTURE = 'adventure';
    case CULTURE = 'culture';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::HIKING => 'Хайкинг',
            self::CITY => 'Городской',
            self::GASTRO => 'Гастрономический',
            self::NATURE => 'Природный',
            self::WINTER => 'Зимний',
            self::ADVENTURE => 'Приключенческий',
            self::CULTURE => 'Культурный',
        };
    }
}
