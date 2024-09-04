<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait PublishRequestTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'publish';

        $this->color(RequestStatus::PUBLISHED->getColor());

        $this->requiresConfirmation();

        $this->label(fn (Request $record) => $record->action?->status !== RequestStatus::RETRACTED ? 'Publish' : 'Republish');

        $this->visible(fn (Request $record) => is_null($status = $record->action?->status) || $status === RequestStatus::RETRACTED);

        $this->icon('heroicon-c-newspaper');

        $this->modalIcon('heroicon-c-newspaper');

        $this->modalDescription('Are you sure you want to publish this request? This will prevent this request from any alteration until it is retracted.');

        $this->successNotificationTitle('Request published successfully');

        $this->action(function (Request $record, self $action) {

            $record->actions()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::PUBLISHED,
                'time' => now(),
            ]);

            Notification::make()
                ->title('Request published')
                ->icon(RequestStatus::PUBLISHED->getIcon())
                ->body('( '.$record->subject.' )'.' has been published for processing')
                ->iconColor(RequestStatus::PUBLISHED->getColor())
                ->sendToDatabase($record->requestor);

            $this->successNotificationTitle('Request successfully published');

            redirect(route('filament.user.resources.requests.index'));
        });
    }
}
