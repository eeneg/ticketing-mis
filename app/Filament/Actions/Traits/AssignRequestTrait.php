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

            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::ASSIGNED,
                'remarks' => (str(' FROM: '.$oldFinal.'<br>'.' TO: '.$assigneesString))->toHtmlString(),
                'time' => now(),
            ]);

            foreach ($listofAssignees as $Assignees) {
                Notification::make()
                    ->title('Request has been reassigned to you')
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
