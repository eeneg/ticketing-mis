<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestQuality;
use App\Enums\RequestStatus;
use App\Enums\RequestTimeliness;
use App\Models\Request;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait ResolveRequestTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'resolve';

        $this->color(RequestStatus::RESOLVED->getColor());

        $this->icon(RequestStatus::RESOLVED->getIcon());

        $this->visible(fn (Request $record) => in_array($record->action?->status, [
            RequestStatus::COMPLETED,
        ]));

        $this->requiresConfirmation();
        $this->form([
            Select::make('Quality')
                ->required()
                ->options(RequestQuality::options()),
            Select::make('Timeliness')
                ->required()
                ->options(RequestTimeliness::options()),
            TextInput::make('Remarks')
                ->placeholder('Provide further description of your experience regarding this request transaction'),
        ]);
        $this->modalDescription('This survey reflects how well the request has been managed by the support and how smooth the process was');
        $this->action(function ($data, Request $record, self $action) {
            $record->action()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::RESOLVED,
                'time' => now(),
            ]);
            $currentAssignees = $data['user_ids'] ?? [];
            foreach ($currentAssignees as $Assignees) {
                Notification::make()
                    ->title('Your assigned request has been resolved')
                    ->body(str($record['subject'].'( '.$record->category->name.' - '.$record->subcategory->name.' )'.'<br>'.'This request will no longer recieve any updates')->toHtmlString())
                    ->sendToDatabase($Assignees);

            }
            $this->successNotificationTitle('Request resolved and surveyed');
        });
    }
}
