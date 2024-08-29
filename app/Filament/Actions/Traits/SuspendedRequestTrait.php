<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;

trait SuspendedRequestTrait
{
    protected function setUp(): void
    {

        parent::setUp();

        $this->name ??= 'suspended';

        $this->color(RequestStatus::SUSPENDED->getColor());

        $this->icon(RequestStatus::SUSPENDED->getIcon());

        $this->visible(fn (Request $record) => $record->action?->status === RequestStatus::STARTED);

        $this->action(function ($data, Request $record, self $action) {
            $action->sendSuccessNotification();

        });

    }
}
