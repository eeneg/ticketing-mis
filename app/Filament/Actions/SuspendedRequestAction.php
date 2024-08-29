<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Traits\SuspendedRequestTrait;
use Filament\Actions\Action;

class SuspendedRequestAction extends Action
{
    use SuspendedRequestTrait;
}
