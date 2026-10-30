<?php

namespace App\Enums;

// Режим игры. Значение enum совпадает с URL (/game/price) и с названием столбца в cars
enum GameMode: string
{
    case Price = 'price';
    case Hp = 'hp';

    public function label(): string
    {
        return match ($this) {
            self::Price => 'Цена',
            self::Hp => 'Мощность',
        };
    }

    public function question(): string
    {
        return match ($this) {
            self::Price => 'У какой машины цена выше?',
            self::Hp => 'У какой машины мощность выше?',
        };
    }

    // Столбец таблицы cars, по которому сравниваем
    public function column(): string
    {
        return $this->value;
    }

    // Красивая строка для показа: "$21 550" или "169 л.с." (неразрывные пробелы, чтобы не переносилось)
    public function format(int $value): string
    {
        $number = number_format($value, 0, '', "\u{00A0}");

        return match ($this) {
            self::Price => '$' . $number,
            self::Hp => $number . "\u{00A0}л.с.",
        };
    }
}
