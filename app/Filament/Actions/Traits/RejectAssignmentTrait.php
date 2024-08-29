<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Enums\UserAssignmentResponse;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait RejectAssignmentTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'reject';

        $this->button();

        $this->icon('heroicon-c-x-circle');

        $this->color('danger');

        $this->close();

        $this->action(function ($record) {
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
                'response' => UserAssignmentResponse::REJECTED,
                'responded_at' => $record->currentUserAssignee->responded->at ?? now(),
            ]);
            Notification::make()
                ->title('Rejected Successfully!')
                ->danger()
                ->send();

                Notification::make()
                ->title('Request Rejected')
                ->body(str("Request of <b>{$record->requestor->name}</b> has been <b>REJECTED</b> by " . auth()->user()->name .'.')->toHtmlString())
                ->icon(RequestStatus::DECLINED->getIcon())
                ->iconColor(RequestStatus::DECLINED->getColor())
                ->sendToDatabase($record->currentUserAssignee->assigner);
        });
        $this->hidden(function ($record) {
            if ($record->currentUserAssignee->responded_at == null) {
                return;
            }

            return $record->currentUserAssignee->responded_at->addMinutes(15)->lt(now());
        });

    }
}
