<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Enums\UserAssignmentResponse;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait AcceptAssignmentTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'accept';

        $this->button();

        $this->icon('heroicon-c-check-circle');

        $this->color('success');

        $this->close();

        $this->hidden(function ($record) {
            return $record->currentUserAssignee->responded_at?->addMinutes(15)->lt(now());
        });

        $this->action(function ($record, $action) {
            if ($record->currentUserAssignee->responded_at?->addMinutes(15)->lt(now())) {
                Notification::make()
                    ->title('No activity for 15 minutes')
                    ->Warning()
                    ->send();

                return;
            }
            $record->currentUserAssignee()->updateOrCreate([
                'user_id' => Auth::id(),
                'assignees.request_id' => $record->id,
            ], [
                'response' => UserAssignmentResponse::ACCEPTED,
                'responded_at' => $record->currentUserAssignee->responded->at ?? now(),
            ]);

            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'response' => RequestStatus::ACCEPTED,
                'status' => RequestStatus::ACCEPTED,
                'time' => now(),
            ]);

            Notification::make()
                ->title('Accepted Successfully!')
                ->success()
                ->send();

            Notification::make()
                ->title('Request Accepted')
                ->body(str("Request of <b>{$record->requestor->name}</b> has been <b>ACCEPTED</b> by ".auth()->user()->name.'.')->toHtmlString())
                ->icon(RequestStatus::ACCEPTED->getIcon())
                ->iconColor(RequestStatus::ACCEPTED->getColor())
                ->sendToDatabase($record->currentUserAssignee->assigner);

            $action->sendSuccessNotification();
        });
    }
}
