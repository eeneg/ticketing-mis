<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;

trait CompletedRequestTrait
{
    protected function setUp(): void
    {

        parent::setUp();

        $this->name ??= 'completed';

        $this->color(RequestStatus::COMPLETED->getColor());

        $this->icon(RequestStatus::COMPLETED->getIcon());

        $this->visible(fn (Request $record) => $record->action?->status === RequestStatus::STARTED);

        $this->action(function ($data, Request $record, self $action) {
            $action->sendSuccessNotification();

        });
    }
}
