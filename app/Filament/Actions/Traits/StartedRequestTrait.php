<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Enums\UserAssignmentResponse;
use App\Models\Request;

trait StartedRequestTrait
{
    protected function setUp(): void
    {

        parent::setUp();

        $this->name ??= 'Start';

        $this->color(RequestStatus::STARTED->getColor());

        $this->icon(RequestStatus::STARTED->getIcon());

        $this->visible(fn (Request $record) => $record->currentUserAssignee?->response === UserAssignmentResponse::ACCEPTED);

        $this->action(function ($data, Request $record, self $action) {
            $action->sendSuccessNotification();

        });
    }
}
