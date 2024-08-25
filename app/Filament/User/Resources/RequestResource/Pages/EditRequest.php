<?php

namespace App\Filament\User\Resources\RequestResource\Pages;

use App\Enums\RequestStatus;
use App\Filament\User\Resources\RequestResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditRequest extends EditRecord
{
    protected static string $resource = RequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Publish')
                ->label(function () {
                    $latestAction = $this->record->actions()->latest()->first();
                    $latestActionStatus = $latestAction?->status;
                    if ($latestActionStatus == RequestStatus::RETRACTED) {
                        return 'Republish';
                    }

                    return 'Publish';
                })
                ->button()
                ->action('publish')
                ->color('success'),
            Action::make('Retract')
                ->button()
                ->action('retract')
                ->color('danger')
                ->visible(function () {
                    $latestAction = $this->record->actions()->latest()->first();
                    $latestActionStatus = $latestAction?->status;

                    return $latestActionStatus == '';

                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),

            $this->getCancelFormAction(),

        ];
    }

    public function publish()
    {
        $this->record->update([
            'office_id' => $this->record->office_id,
            'category_id' => $this->record->category_id,
            'subcategory_id' => $this->record->subcategory_id,
            'remarks' => $this->record->remarks,
            'availability_from' => $this->record->availability_from,
            'availability_to' => $this->record->availability_to,
            'published_at' => now(),

        ]);
        $this->record->actions()->create([
            'request_id' => $this->record->id,
            'user_id' => Auth::id(),
            'status' => RequestStatus::PUBLISHED,
            'remarks' => $this->record->remarks,
            'time' => now(),
        ]);
        Notification::make()
            ->title('Request Published Successfully')
            ->success()
            ->send();
    }

    public function retract()
    {
        $this->record->update([
            'published_at' => null,
        ]);
        $this->record->actions()->create([
            'request_id' => $this->record->id,
            'user_id' => Auth::id(),
            'status' => RequestStatus::RETRACTED,
            'remarks' => $this->record->remarks,
            'time' => now(),
        ]);
        Notification::make()
            ->title('Request Retracted Successfully')
            ->success()
            ->send();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['requestor_id'] = Auth::id();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->sanitize();
    }
}
