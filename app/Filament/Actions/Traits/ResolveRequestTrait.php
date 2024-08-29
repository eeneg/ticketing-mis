<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait ResolveRequestTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'resolve';

        $this->color(RequestStatus::RESOLVED->getColor());

        $this->icon(RequestStatus::RESOLVED->getIcon());

        $this->visible(fn (Request $record) => in_array($record->action?->status, [
            RequestStatus::COMPLETED,
        ]));

        $this->requiresConfirmation();

        $this->action(function ($data, Request $record, self $action) {
            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::RESOLVED,
                'time' => now(),
            ]);

            $recipientUser = $record->requestor_id;
            $subject = $record['subject'];
            $category = $record->category->name;
            $subcategory = $record->subcategory->name;
            $currentAssignees = $data['user_ids'] ?? [];

            // Notification::make()
            //     ->title('This Request has been resolved')
            //     ->icon('heroicon-c-clipboard-document-check')
            //     ->iconColor(RequestStatus::RESOLVED->getColor())
            //     ->body(str($subject.'( '.$category.' - '.$subcategory.' )'.'<br>'.'This request will no longer recieve any updates')->toHtmlString())
            //     ->sendToDatabase($recipientUser);

            foreach ($currentAssignees as $Assignees) {
                Notification::make()
                    ->title('Your assigned request has been resolved')
                    ->body(str($subject.'( '.$category.' - '.$subcategory.' )'.'<br>'.'This request will no longer recieve any updates')->toHtmlString())
                    ->sendToDatabase($Assignees);

            }
            $action->sendSuccessNotification();
        });
    }
}
