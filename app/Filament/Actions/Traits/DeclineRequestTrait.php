<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use App\Models\User;
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

            $time = now();
            $from = User::where('id', Auth::id())->value('name');
            $subject = $record->subject;
            $recipientUser = $record->requestor_id;
            $remarks = $data['remarks'];

            Notification::make()
                ->title('The request has been declined by the officers')
                ->icon('heroicon-c-no-symbol')
                ->iconColor(RequestStatus::DECLINED->getColor())
                ->body(str($from.' '.$subject.' '.$time.'<br>'.'Reasoning : '.$remarks)->toHtmlString())
                ->sendToDatabase($recipientUser);
            $action->sendSuccessNotification();

        });

        $this->successNotificationTitle('Request declined');
    }
}
