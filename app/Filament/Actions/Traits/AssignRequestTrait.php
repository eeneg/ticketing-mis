<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            RequestStatus::APPROVED,
        ]));

        $this->form([
            CheckboxList::make('user_ids')
                ->columns(2)
                ->label('Assignees')
                ->default(fn ($record) => $record ? $record->assignees()->pluck('user_id')->toArray() : [])
                ->searchable()
                ->hiddenLabel()
                ->options(User::query()->where('role', 'support')->pluck('name', 'id')),
        ]);

        $this->action(function ($data, $record, self $action) {
            $formerAssignees = User::find($record->assignees->pluck('user_id'));

            $userIds = $data['user_ids'] ?? [];

            $record->assignees()->whereNotIn('user_id', $userIds)->delete();
            // drop unassigned support

            $upsert_records = collect($userIds)->map(function ($id) use ($record) {
                return [
                    'assigner_id' => Auth::id(),
                    'request_id' => $record->id,
                    'user_id' => $id,
                    'response' => 'pending',
                ];
            })->toArray();

            $record->assignees()->upsert(
                $upsert_records,
                ['request_id', 'user_id'],
                ['user_id'],
            );

            $listofAssignees = $data['user_ids'] ?? [];
            $assigneeNames = User::whereIn('id', $listofAssignees)->pluck('name')->toArray();
            $assigneesString = implode(', ', $assigneeNames);
            $oldAssignees = $record->assignees->pluck('user_id')->toArray();
            $oldNames = User::whereIn('id', $oldAssignees)->pluck('name')->toArray();
            $oldFinal = implode(', ', $oldNames);
            $remarks = '';
            // if ($record->request_id) {
            //     $existsInAssigneeTable = DB::table('assignees')
            //         ->where('request_id', $record->request_id)
            //         ->exists();

            //     if (! $existsInAssigneeTable) {
            //         $remarks = str('Assigned to: '.$assigneesString)->toHtmlString();
            //     } else {
            //         $remarks = str('FROM: '.$oldFinal.'<br>'.' TO: '.$assigneesString)->toHtmlString();
            //     }

            // }
            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::ASSIGNED,
                'remarks' => str('FROM: '.$oldFinal.'<br>'.' TO: '.$assigneesString)->toHtmlString(),
                'time' => now(),
            ]);
            // $request_ids = $record->assignees->pluck('request_id')->toArray();
            // $remarks = (function ($record, $oldAssignees, $assigneesString, $oldFinal, $request_ids) {
            //     if ($record->request_id => in_array([$request_ids])) {
            //         return str('Assigned to: '.$assigneesString)->toHtmlString();
            //     }

            //     return str('FROM: '.$oldFinal.'<br>'.' TO: '.$assigneesString)->toHtmlString();
            // })($oldAssignees, $assigneesString, $oldFinal);

            // $this->hidden(fn ($record) => in_array($record->action->status, [
            //     RequestStatus::RESOLVED,
            //     RequestStatus::COMPLETED,
            //     RequestStatus::APPROVED,
            // ]) || in_array($record->currentUserAssignee?->response, [
            //     UserAssignmentResponse::PENDING,
            //     UserAssignmentResponse::REJECTED,
            // ]));

            $subject = $record->subject;
            $category = $record->category->name;
            $office = $record->office->name;
            $availability_from = Carbon::parse($record['availability_from'])->format('j\t\h \o\f F \a\t h:i:s A');
            $availability_to = Carbon::parse($record['availability_to'])->format('j\t\h \o\f F \a\t h:i:s A');

            foreach ($listofAssignees as $Assignees) {
                Notification::make()
                    ->title('A request has been reassigned to you')
                    ->body(str($office.' - '.$subject.'( '.$category.' )'.'<br>'.'Available from:'.$availability_from.'<br>'.'Available to: '.' '.$availability_to)->toHtmlString())
                    ->icon('heroicon-c-arrow-path')
                    ->iconColor(RequestStatus::ASSIGNED->getColor())
                    ->sendtoDatabase(User::find($Assignees));
            }

            Notification::make()
                ->title('Request has been reassigned')
                ->success()
                ->send();
            $action->sendSuccessNotification();

        });
    }
}
