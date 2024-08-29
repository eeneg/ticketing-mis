<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;

trait CompliedRequestTrait
{
    protected function setUp(): void
    {

        parent::setUp();

        $this->name ??= 'complied';

        $this->color(RequestStatus::COMPLIED->getColor());

        $this->icon(RequestStatus::COMPLIED->getIcon());

        $this->visible(fn (Request $record) => $record->action?->status === RequestStatus::SUSPENDED);

        $this->action(function ($data, Request $record, self $action) {
            $action->sendSuccessNotification();

        });
    }
}
