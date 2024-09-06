<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait DenyCompletedTrait
{
    protected function setUp(): void
    {

        parent::setUp();

        $this->name ??= 'deny';

        $this->color(RequestStatus::DENIED->getColor());

        $this->icon(RequestStatus::DENIED->getIcon());

        $this->visible(fn (Request $record) => $record->action?->status === RequestStatus::COMPLETED);

        $this->requiresConfirmation();

        $this->action(function ($record, $data, $action) {
            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::DENIED,
                'time' => now(),
            ]);
            Notification::make()
                ->title('Completion denied')
                ->icon(RequestStatus::DENIED->getIcon())
                ->iconColor(RequestStatus::DENIED->getColor())
                ->body($record->category->name.' ( '.$record->subcategory->name.' ) '.'</br>'.auth()->user()->name.' has denied the completion of this request')
                ->sendToDatabase($record->assignees);
            $this->successNotificationTitle('Denied request completion');
        });
    }
}
