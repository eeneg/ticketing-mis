<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait AssignRequestTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'assign';

        $this->color(RequestStatus::ASSIGNED->getColor());

        $this->icon(RequestStatus::ASSIGNED->getIcon());

        $this->visible(fn (Request $record) => in_array($record->action?->status, [
            RequestStatus::ASSIGNED,
            RequestStatus::REJECTED,
            RequestStatus::APPROVED,
        ]));

        $this->form([
            CheckboxList::make('assignees')
                ->columns(2)
                ->label('Assignees')
                ->default(fn ($record) => $record ? $record->assignees()->pluck('user_id')->toArray() : [])
                ->searchable()
                ->hiddenLabel()
                ->options(function ($record) {
                    return
                          User::query()
                              ->where('role', 'support')
                              ->where('office_id', $record->office->id)
                              ->pluck('name', 'id');
                }),
        ]);

        $this->action(function ($data, $record, self $action) {
            $from = implode(' and ', $record?->assignees()->pluck('name')->toArray());
            $record->assignees()->detach();
            $record->assignees()->attach(
                collect($data['assignees'])->mapWithKeys(function ($id) use ($record, $data) {
                    Notification::make()
                        ->title('New Request Assigned')
                        ->icon('heroicon-o-check-circle')
                        ->iconColor(RequestStatus::APPROVED->getColor())
                        ->body(str('Assigned to : '.implode(' and ', User::whereIn('id', $data['assignees'])->pluck('name')->toArray()).'</br>'.$record->office->acronym.' - '.$record->subject.' ( '.$record->category->name.' )')->toHtmlString())
                        ->sendToDatabase(User::find($id));

                    return [
                        $id => [
                            'assigner_id' => Auth::id(),
                            'created_at' => now(),
                        ],
                    ];
                })->toArray()
            );
            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::ASSIGNED,
                'remarks' => 'Assigned '.($from ? ' from '.$from : '').' to '.implode(' and ', User::whereIn('id', $data['assignees'])->pluck('name')->toArray()),
                'time' => now(),
            ]);

            Notification::make()
                ->title(str("Your request <b>{$record->subject}</b> has been assigned")->toHtmlString())
                ->icon(RequestStatus::ASSIGNED->getIcon())
                ->iconColor(RequestStatus::ASSIGNED->getColor())
                ->body('Assigned '.($from ? ' from '.$from : '').' to '.implode(' and ', User::whereIn('id', $data['assignees'])->pluck('name')->toArray()))
                ->sendToDatabase($record->requestor);

            Notification::make()
                ->title('Request has been reassigned')
                ->success()
                ->send();

        });
    }
}
