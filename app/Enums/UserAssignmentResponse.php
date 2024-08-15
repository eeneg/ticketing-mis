<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserAssignmentResponse: string implements HasLabel, HasColor
{
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case PENDING = 'pending';

    public function getLabel(): ?string
    {
        return ucfirst($this->value);
    }

    public function getColor(): ?string
    {
        return match($this) {
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
            self::PENDING => 'warning',
            default=> 'gray'
        };
    }
}

