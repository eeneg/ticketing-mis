<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\Action;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('change_password')
                ->requiresConfirmation()
                ->modalDescription()
                ->icon('heroicon-s-lock-closed')
                ->form([
                    TextInput::make('password')
                        ->password()
                        ->markAsRequired()
                        ->rules('required')
                        ->revealable(),
                    TextInput::make('confirm_password')
                        ->password()
                        ->markAsRequired()
                        ->rules('required')
                        ->same('password')
                        ->revealable(),
                ])
                ->closeModalByClickingAway(false)
                ->action(function (array $data, User $record) {
                    $record->update($data);

                    Notification::make()
                        ->title('Pasword updated successfully')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
