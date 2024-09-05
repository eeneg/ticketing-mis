<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait StartedRequestTrait
{
    protected function setUp(): void
    {

        parent::setUp();

        $this->name ??= 'Start';

        $this->color(RequestStatus::STARTED->getColor());

        $this->icon(RequestStatus::STARTED->getIcon());

        $this->visible(fn ($record) => $record->assignees()->wherePivot('response', 'accepted')->count() === $record->assignees->count() || $record->action->status === RequestStatus::COMPLIED);

        $this->requiresConfirmation();

        $this->action(function ($data, Request $record, self $action) {
            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::STARTED,
                'time' => now(),
            ]);

            Notification::make()
                ->title('Support has started this ticket')
                ->body('Assigned support has started working on this ticket')
                ->icon(RequestStatus::STARTED->getIcon())
                ->iconColor(RequestStatus::STARTED->getColor())
                ->sendToDatabase($record->requestor);
            $this->successNotificationTitle('Request started');

        });
    }
}
