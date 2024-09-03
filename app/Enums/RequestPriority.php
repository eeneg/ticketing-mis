<?php

namespace App\Enums;

enum RequestPriority: string
{
    case ONE = '1';
    case TWO = '2';
    case THREE = '3';
    case FOUR = '4';
    case FIVE = '5';

    public function getDescription(): ?string
    {
        return match ($this) {
            self::ONE => 'Minimal Priority',
            self::TWO => 'Low Priority',
            self::THREE => 'Medium Priority',
            self::FOUR => 'High Priority',
            self::FIVE => 'Critical Priority',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => $case->value.' - '.$case->getDescription(), self::cases());
    }
}
