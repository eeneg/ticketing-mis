<?php

namespace App\Filament\User\Resources\RequestResource\Pages;

use App\Enums\RequestStatus;
use App\Filament\Actions\PublishRequestAction;
use App\Filament\Actions\RetractRequestAction;
use App\Filament\User\Resources\RequestResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditRequest extends EditRecord
{
    protected static string $resource = RequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PublishRequestAction::make(),
            RetractRequestAction::make(),
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
