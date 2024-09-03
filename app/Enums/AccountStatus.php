<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AccountStatus: string implements HasLabel
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function getLabel(): ?string
    {
        return ucfirst($this->value);
    }
}
