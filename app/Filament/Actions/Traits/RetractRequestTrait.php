<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use Filament\Forms\Components\RichEditor;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Auth;

trait RetractRequestTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'retract';

        $this->color(RequestStatus::RETRACTED->getColor());

        $this->visible(fn (Request $record) => $record->action?->status === RequestStatus::PUBLISHED);

        $this->icon('heroicon-c-newspaper');

        $this->modalAlignment(Alignment::Left);

        $this->modalIcon('heroicon-c-newspaper');

        $this->modalDescription('Are you sure you want to retract this request?');

        $this->modalWidth('3xl');

        $this->successNotificationTitle('Request retracted successfully');

        $this->form([
            RichEditor::make('remarks')
                ->columnSpan(2)
                ->label('Remarks')
                ->placeholder('Please provide a reason for retracting this request...')
                ->required(),
        ]);

        $this->action(function (Request $record, self $action, array $data) {
            $record->actions()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::RETRACTED,
                'time' => now(),
                'remarks' => $data['remarks'],
            ]);

            $action->sendSuccessNotification();
        });
    }
}
