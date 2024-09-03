<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait DeclineRequestTrait
{
    protected function setUp(): void
    {

        parent::setUp();

        $this->name ??= 'decline';

        $this->color(RequestStatus::DECLINED->getColor());

        $this->icon(RequestStatus::DECLINED->getIcon());

        $this->visible(fn (Request $record) => $record->action?->status === RequestStatus::PUBLISHED);

        $this->requiresConfirmation();

        $this->modalWidth('3xl');

        $this->form([
            RichEditor::make('remarks')
                ->label('Declining Request')
                ->placeholder('Please provide a reason for declining this request'),
        ]);

        $this->action(function ($record, $data, self $action) {
            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::DECLINED,
                'remarks' => $data['remarks'],
                'time' => now(),
            ]);

            Notification::make()
                ->title('The request has been declined by the officers')
                ->icon('heroicon-c-no-symbol')
                ->iconColor(RequestStatus::DECLINED->getColor())
                ->body(str(auth()->user()->name.' declined : '.' '.$record->subject.'</br>'.$data['remarks'])->toHtmlString())
                ->sendToDatabase($record->requestor);
            $action->sendSuccessNotification();

        });

        $this->successNotificationTitle('Request declined');
    }
}
