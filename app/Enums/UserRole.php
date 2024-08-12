<?php

namespace App\Enums;

enum UserRole: string
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
