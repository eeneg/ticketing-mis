<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use App\Models\User;
use Carbon\Carbon;
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
            $record->assignees()->detach();

            $record->assignees()->attach(
                collect($data['assignees'])->mapWithKeys(function ($id) use ($record) {
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
                        ],
                    ];
                })->toArray()
            );
            // List of old assignees
            $listofAssignees = $data['user_ids'] ?? [];
            $assigneeNames = User::whereIn('id', $listofAssignees)->pluck('name')->toArray();
            $assigneesString = implode(', ', $assigneeNames);
            // List of new assignees
            $oldAssignees = $record['user_ids'] ?? [];
            $oldNames = User::whereIn('id', $oldAssignees)->pluck('name')->toArray();
            $oldFinal = implode(', ', $oldNames);
            $remarks = '';
            // check there are assigned support
            $remarks = empty($userIds)
                ? str('Assigned to: '.$assigneesString)->toHtmlString()
                : str('Assigned from: '.$oldFinal.'<br>'.' to: '.$assigneesString)->toHtmlString();

            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::ASSIGNED,
                'remarks' => $remarks,
                'time' => now(),
            ]);
            $availability_from = Carbon::parse($record['availability_from'])->format('j\t\h \o\f F \a\t h:i:s A');
            $availability_to = Carbon::parse($record['availability_to'])->format('j\t\h \o\f F \a\t h:i:s A');
            $assigned = empty($userIds)
                ? str('assigned')->toHtmlString()
                : str('reassigned')->toHtmlString();

            foreach ($listofAssignees as $Assignees) {
                Notification::make()
                    ->title('A new request has been '.$assigned.' to you')
                    ->body(str("<b>{$record->office->acronym}</b>".' - '."<i>$record->subject</i>".' ( '.$record->category->name.' )'.'<br>'.'Available from:'.$availability_from.'<br>'.'Available to: '.' '.$availability_to)->toHtmlString())
                    ->icon('heroicon-c-arrow-path')
                    ->iconColor(RequestStatus::ASSIGNED->getColor())
                    ->sendtoDatabase(User::find($Assignees));
            }
            Notification::make()
                ->title('Your request ('."<b>$record->subject</b>".') has been '.$assigned.'.')
                ->icon(RequestStatus::ASSIGNED->getIcon())
                ->iconColor(RequestStatus::ASSIGNED->getColor())
                ->body($assigned.' to: '.$assigneesString)
                ->sendToDatabase($record->requestor);
            //
            Notification::make()
                ->title('Request has been reassigned')
                ->success()
                ->send();
            $action->sendSuccessNotification();

        });
    }
}
