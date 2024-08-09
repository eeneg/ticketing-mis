<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('change_password')
                ->label('Change Password')
                ->form([
                    TextInput::make('password')
                        ->password()
                        ->minLength(8)
                        ->maxLength(255)
                        ->required()
                        ->revealable(),
                    TextInput::make('confirm_password')
                        ->password()
                        ->minLength(8)
                        ->maxLength(255)
                        ->required()
                        ->revealable()
                        ->same('password'),
                ])
                ->modalWidth(MaxWidth::Large)
                ->closeModalByClickingAway(false)
                ->action(function (array $data) {
                    $this->record->update($data);
                    Notification::make()
                        ->title('Change Password Successfully')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
