<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RequestStatus: string implements HasLabel
{
    case HOLD = 'HOLD';
    case DONE = 'DONE';
    case CANCEL = 'CANCEL';

    public function getLabel(): ?string
    {
        return ucfirst($this->value);
    }

    public function getColor(): ?string
    {
        return match($this) {
            self::HOLD => 'success',
            self::DONE => 'danger',
            self::CANCEL => 'warning',
            default=> 'gray'
        };
    }
}
