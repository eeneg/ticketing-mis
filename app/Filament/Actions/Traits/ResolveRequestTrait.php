<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Category;
use App\Models\Request;
use App\Models\Subcategory;
use App\Models\User;
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

        // $this->visible(fn (Request $record) => in_array($record->action?->status, [
        //     RequestStatus::COMPLETED,
        // ]));
        // ONLY VISIBILE WHEN THE USER SAYS THE REQUEST HAS BEEN COMPLETED
        $this->action(function ($data, Request $record, self $action) {
            $record->actions()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::RESOLVED,
                'time' => now(),
            ]);
            $recipientUser = User::find($record->requestor_id);
            $subject = $record['subject'];
            $category = Category::where('id', $record['category_id'])->value('name');
            $subcategory = Subcategory::where('id', $record['subcategory_id'])->value('name');
            $currentAssignees = $data['user_ids'] ?? [];

            Notification::make()
            // User notification
                ->title('This Request has been resolved')
                ->icon('heroicon-c-clipboard-document-check')
                ->iconColor(RequestStatus::RESOLVED->getColor())
                ->body(str($subject.'( '.$category.' - '.$subcategory.' )'.'<br>'.'This request will no longer recieve any updates')->toHtmlString())
                ->sendToDatabase($recipientUser);
            foreach ($currentAssignees as $Assignees) {
                // Assignee Notification
                Notification::make()
                    ->title('Your assigned request has been resolved')
                    ->body(str($subject.'( '.$category.' - '.$subcategory.' )'.'<br>'.'This request will no longer recieve any updates')->toHtmlString())
                    ->sendToDatabase($Assignees);
            }
            $action->sendSuccessNotification();
        });
    }
}
