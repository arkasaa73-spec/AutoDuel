<?php

namespace App\Enums;

// Уровень сложности. Задаёт, во сколько раз могут отличаться значения двух машин в паре
enum GameLevel: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';

    public function label(): string
    {
        return match ($this) {
            self::Easy => 'Новичок',
            self::Medium => 'Опытный',
            self::Hard => 'Профи',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Easy => 'Значения отличаются в 1,8-4 раза. Хватит общего понимания, какие бывают машины.',
            self::Medium => 'Значения отличаются на 25-80%. Нужно неплохо знать автомобильный рынок.',
            self::Hard => 'Значения отличаются всего на 3-25%. Ошибиться легко даже эксперту.',
        };
    }

    // Минимальное отношение большего значения к меньшему (всегда больше 1, чтобы не было равных пар)
    public function minRatio(): float
    {
        return match ($this) {
            self::Easy => 1.8,
            self::Medium => 1.25,
            self::Hard => 1.03,
        };
    }

    // Максимальное отношение: именно оно отсекает пары вроде Bugatti против Corolla
    public function maxRatio(): float
    {
        return match ($this) {
            self::Easy => 4.0,
            self::Medium => 1.8,
            self::Hard => 1.25,
        };
    }
}
