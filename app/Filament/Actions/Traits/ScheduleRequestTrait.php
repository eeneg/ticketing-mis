<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;

trait ScheduleRequestTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'schedule';

        $this->icon('heroicon-s-clock');

        $this->label('Set target Date and Time');

        $this->modalWidth(MaxWidth::Large);

        $this->form([
            DatePicker::make('target_date')
                ->required()
                ->minDate(fn ($record) => $record->availability_from)
                ->maxDate(fn ($record) => $record->availability_to),
            TimePicker::make('target_time')
                ->required()
                ->seconds(false)
                ->placeholder('12:00')
                ->rule(fn () => function ($a, $v, $f) {
                    if ($v < '08:00' || $v > '17:00') {
                        $f('Invalid time');
                    }
                }),
        ]);

        $this->action(function ($record, $data) {
            $from = $record->target_date.' '.$record->target_time;
            $record->update($data);
            $record->action()->create([
                'user_id' => Auth::id(),
                'actions.request_id' => $record->id,
                'status' => RequestStatus::SCHEDULED->value,
                'time' => now(),
                'remarks' => 'Scheduled'.($from ? ' from '.$from : '').' to '.$data['target_date'].' '.$data['target_time'],
            ]);
            Notification::make()
                ->title('Scheduled Successfully!')
                ->success()
                ->send();

            Notification::make()
                ->title('Request Scheduled')
                ->body(str("Your request has been scheduled to <b>{$data['target_date']}</b> at <b>{$data['target_time']}</b> by ".auth()->user()->name.'.')->toHtmlString())
                ->icon(RequestStatus::SCHEDULED->getIcon())
                ->iconColor(RequestStatus::SCHEDULED->getColor())
                ->sendToDatabase($record->requestor);
        });
    }
}
