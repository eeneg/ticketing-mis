<?php

namespace App\Enums;

enum RequestTimeliness: string
{
    case ONE = '1';
    case TWO = '2';
    case THREE = '3';
    case FOUR = '4';
    case FIVE = '5';

    public function getDescription(): ?string
    {
        return match ($this) {
            self::ONE => 'Extremely Late',
            self::TWO => 'Moderately Late',
            self::THREE => 'Slightly Late',
            self::FOUR => 'Almost on Time',
            self::FIVE => 'On Time',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => $case->value.' - '.$case->getDescription(), self::cases());
    }
}
