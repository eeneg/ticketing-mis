<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Models\Request;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait ApproveRequestTrait
{
    protected function setUp(): void
    {
        parent::setUP();

        $this->name ??= 'approve';

        $this->color(RequestStatus::APPROVED->getColor());

        $this->icon(RequestStatus::APPROVED->getIcon());

        $this->visible(fn (Request $record) => $record->action?->status === RequestStatus::PUBLISHED);

        $this->form([
            Select::make('priority')
                ->placeholder('Provide an estimate on how time crucial the task is.')
                ->options(RequestPriority::options()
                )
                ->required(),

            RichEditor::make('remarks')
                ->label('Remarks')
                ->placeholder('Provide further details regarding this request'),

            Select::make('assignees')
                ->label('Assignees')
                ->placeholder('Select a support to assign this request to.')
                ->options(function($record) {
                      return
                            User::query()
                             ->where('role','support')
                             ->where('office_id',$record->office->id)
                             ->pluck('name','id');
                })
                ->multiple(),
        ]);

        $this->action(function ($data, $record, self $action) {
            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::APPROVED,
                'remarks' => $data['remarks'],
                'time' => now(),
            ]);

            $record->assignees()->attach(
                collect($data['assignees'])->mapWithKeys(function ($id) use ($record){
                    Notification::make()
                        ->title('New Request Assigned')
                        ->icon('heroicon-o-check-circle')
                        ->iconColor(RequestStatus::APPROVED->getColor())
                        ->body($record->office->acronym.' - '.$record->subject.'( '.$record->category->name)
                        ->sendToDatabase(User::find($id));
                    return [
                        $id => [
                            'assigner_id' => Auth::id(),
                            'created_at' => now(),
                        ]
                    ];
                    })->toArray()
            );

            $assigneeNames = User::whereIn('id', $data['assignees'])->pluck('name')->toArray();
            $assigneesString = implode(', ', $assigneeNames);

            if (empty($userIds)) {
                Notification::make()
                    ->title('The request is being processed and is to be assigned soon')
                    ->icon('heroicon-o-check-circle')
                    ->iconColor(RequestStatus::APPROVED->getColor())
                    ->sendToDatabase($record->requestor);
            } else {
                Notification::make()
                    ->title('The request has been assigned to '.$assigneesString.' by '.auth()->user()->name.' and is awaiting further process')
                    ->icon('heroicon-o-check-circle')
                    ->iconColor(RequestStatus::APPROVED->getColor())
                    ->sendToDatabase($record->requestor);
            }

            Notification::make()
                ->title('Request has been assigned')
                ->success()
                ->send();
            $record->update(['priority' => $data['priority']]);

        });

    }
}
