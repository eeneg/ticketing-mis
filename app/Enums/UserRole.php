<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case ADMIN = 'admin';
    case USER = 'user';
    case OFFICER = 'officer';
    case SUPPORT = 'support';

    public function getLabel(): ?string
    {
        return ucfirst($this->value);
    }
}

