<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Models\Category;
use App\Models\Office;
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
                ->options(RequestPriority::options())
                ->required(),

            RichEditor::make('remarks')
                ->label('Remarks')
                ->required(),

            Select::make('user_ids')
                ->label('Assignees')
                ->options(User::query()->where('role', 'support')->pluck('name', 'id'))
                ->multiple(),
        ]);

        $this->action(function ($data, $record, self $action) {
            $userIds = $data['user_ids'] ?? [];

            $record->assignees()->createMany(
                collect($userIds)->map(function ($id) use ($record) {
                    return [
                        'assigner_id' => Auth::id(),
                        'request_id' => $record->id,
                        'user_id' => $id,
                        'response' => 'pending',
                    ];
                })
            );

            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::APPROVED,
                'remarks' => $data['remarks'],
                'time' => now(),
            ]);

            $subject = $record['subject'];
            $recipientAssignees = $data['user_ids'] ?? [];
            $recipientUser = User::find($record->requestor_id);
            $office = Office::where('id', $record->office_id)->value('acronym');
            $category = Category::where('id', $record->category_id)->value('name');
            $availability_from = Carbon::parse($record['availability_from'])->format('j\t\h \o\f F \a\t h:i:s A');
            $availability_to = Carbon::parse($record['availability_to'])->format('j\t\h \o\f F \a\t h:i:s A');

            foreach ($recipientAssignees as $Assignees) {
                Notification::make()
                    ->title('New Request Assigned')
                    ->icon('heroicon-o-check-circle')
                    ->iconColor(RequestStatus::APPROVED->getColor())
                    ->body(str($office.' - '.$subject.'( '.$category.' )'.'<br>'.'Available from:'.$availability_from.'<br>'.'Available to: '.' '.$availability_to)->toHtmlString())
                    ->sendToDatabase(User::find($Assignees));
            }

            Notification::make()
                ->title('The request is being processed')
                ->icon('heroicon-o-check-circle')
                ->iconColor(RequestStatus::APPROVED->getColor())
                ->sendToDatabase($recipientUser);

            Notification::make()
                ->title('Request has been assigned')
                ->success()
                ->send();
            $record->update(['priority' => $data['priority']]);

            $action->sendSuccessNotification();

        });

    }
}
