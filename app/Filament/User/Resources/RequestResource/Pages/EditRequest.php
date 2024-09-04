<?php

namespace App\Filament\User\Resources\RequestResource\Pages;

use App\Enums\RequestStatus;
use App\Filament\Actions\AmmendRecentActionAction;
use App\Filament\Actions\PublishRequestAction;
use App\Filament\Actions\RetractRequestAction;
use App\Filament\User\Resources\RequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditRequest extends EditRecord
{
    protected static string $resource = RequestResource::class;

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        abort_unless(is_null($this->record->action) || $this->record->action->status === RequestStatus::RETRACTED, 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            AmmendRecentActionAction::make(),
            PublishRequestAction::make(),
            RetractRequestAction::make(),
            Actions\DeleteAction::make(),
        ];
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
