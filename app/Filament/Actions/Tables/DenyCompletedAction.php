<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Traits\DenyCompletedTrait;
use Filament\Tables\Actions\Action;

class DenyCompletedAction extends Action
{
    use DenyCompletedTrait;
}
