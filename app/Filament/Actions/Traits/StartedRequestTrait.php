<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Enums\UserAssignmentResponse;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;

trait StartedRequestTrait
{
    protected function setUp(): void
    {

        parent::setUp();

        $this->name ??= 'Start';

        $this->color(RequestStatus::STARTED->getColor());

        $this->icon(RequestStatus::STARTED->getIcon());

        $this->visible(fn (Request $record) => $record->currentUserAssignee?->response === UserAssignmentResponse::ACCEPTED);

        $this->hidden(fn (Request $record) => $record->action?->status === RequestStatus::STARTED || $record->action?->status === RequestStatus::RESOLVED);

        $this->requiresConfirmation();

        $this->action(function ($data, Request $record, self $action) {
            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::STARTED,
                'time' => now(),
            ]);
            $action->sendSuccessNotification();

        });
    }
}
