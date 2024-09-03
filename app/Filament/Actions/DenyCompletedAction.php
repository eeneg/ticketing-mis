<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Traits\DenyCompletedTrait;
use Filament\Actions\Action;

class DenyCompletedAction extends Action
{
    use DenyCompletedTrait;
}
