<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Traits\SuspendedRequestTrait;
use Filament\Tables\Actions\Action;

class SuspendedRequestAction extends Action
{
    use SuspendedRequestTrait;
}
