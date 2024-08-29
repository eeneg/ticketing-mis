<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait AdjustRequestTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'adjust';

        $this->label('Set Difficulty');

        $this->icon('heroicon-s-adjustments-vertical');

        $this->action(function ($record, $data) {
            $from = $record->difficulty;

            $record->update(['difficulty' => $data['diff']]);

            $record->action()->create([
                'user_id' => Auth::id(),
                'actions.request_id' => $record->id,
                'status' => RequestStatus::ADJUSTED->value,
                'time' => now(),
                'remarks' => 'Difficulty'.($from ? ' from '.$from : '').' to '.$data['diff'],
            ]);

            Notification::make()
                ->title('Difficulty Adjusted')
                ->body(str("Request difficulty of <b>{$record->requestor->name}</b> has adjusted ".($from ? ' from '.$from : '').' by '.auth()->user()->name.'.')->toHtmlString())
                ->icon(RequestStatus::ADJUSTED->getIcon())
                ->iconColor(RequestStatus::ADJUSTED->getColor())
                ->sendToDatabase($record->currentUserAssignee->assigner);
        });

        $this->form([
            Select::make('diff')
                ->label('Difficulty Level')
                ->options([
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                ]),
        ]);
    }
}
