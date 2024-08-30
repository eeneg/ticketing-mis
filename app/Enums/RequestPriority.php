<?php

namespace App\Enums;

enum RequestPriority: string
{
    case ONE = '1';
    case TWO = '2';
    case THREE = '3';
    case FOUR = '4';
    case FIVE = '5';

    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::ONE => 'Not important.',
            self::TWO => 'Can be ignored',
            self::THREE => 'Normal Request',
            self::FOUR => 'Important',
            self::FIVE => 'Highly Important.',
        };
    }
}
