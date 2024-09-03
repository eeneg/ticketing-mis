<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use App\Models\User;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait ExtensionRequestTrait
{
    protected function setUp(): void
    {

        parent::setUp();

        $this->name ??= 'extension';

        $this->color(RequestStatus::EXTENDED->getColor());

        $this->icon(RequestStatus::EXTENDED->getIcon());

        $this->visible(fn (Request $record) => $record->action?->status === RequestStatus::COMPLETED);

        $this->form([
            RichEditor::make('remarks')
                ->columnSpanFull()
                ->label('Remarks')
                ->placeholder('Please provide a reason for creating an extension for this request')
                ->required(),
        ]);

        $this->action(function ($record, $data, $action) {
            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::EXTENDED,
                'remarks' => $data['remarks'],
                'time' => now(),
            ]);

            $assigneeId = $record->assignees->pluck('user_id')->toArray();

            foreach ($assigneeId as $assigned) {
                Notification::make()
                    ->title('User requires extension')
                    ->icon(RequestStatus::EXTENDED->getIcon())
                    ->iconColor(RequestStatus::EXTENDED->getColor())
                    ->body($record->category->name.' ( '.$record->subcategory->name.' ) '.'</br>'.auth()->user()->name.' has approved the completion of this request')
                    ->sendToDatabase(User::find($assigned));
            }
            $this->successNotificationTitle('Requested for extension.');
        });
    }
}
