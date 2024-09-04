<?php

namespace App\Filament\Auth;

use App\Enums\RequestStatus;
use App\Models\Office;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register;

class RegistrationPage extends Register
{
    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->notifyAdmin($data);

            $this->callHook('afterRegister');

            return $user;
        });

        event(new Registered($user));

        Notification::make()
            ->title('Registered Successfully')
            ->success()
            ->send();

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

    public function form(Form $form): Form
    {
        return $this->makeForm()
            ->schema([
                $this->getAvatarFormComponent(),
                Group::make()
                    ->columns(2)
                    ->schema([
                        Group::make()
                            ->schema([
                                $this->getNameFormComponent(),
                                $this->getEmailFormComponent(),
                                $this->getPasswordFormComponent(),
                            ]),
                        Group::make()
                            ->schema([
                                $this->getOfficeFormComponent(),
                                $this->getNumberFormComponent(),
                                $this->getPasswordConfirmationFormComponent(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar')
            ->alignCenter()
            ->avatar()
            ->directory('avatars');
    }

    protected function getOfficeFormComponent(): Component
    {
        return Select::make('office_id')
            ->native(false)
            ->searchable()
            ->options(Office::query()->pluck('acronym', 'id'))
            ->getSearchResultsUsing(fn ($search): array => Office::where('name', 'like', "{$search}")->pluck('acronym', 'id')->toArray())
            ->getOptionLabelUsing(fn ($value): ?string => Office::find($value)?->acronym);
    }

    protected function getNumberFormComponent(): Component
    {
        return TextInput::make('number')
            ->required()
            ->placeholder('9xx xxx xxxx')
            ->mask('999 999 9999')
            ->prefix('+63 ')
            ->rule(fn () => function ($a, $v, $f) {
                if (! preg_match('/^9.*/', $v)) {
                    $f('Incorrect number format');
                }
            });
    }

    private function notifyAdmin(array $data): Notification
    {
        return Notification::make()
            ->title('Newly Registered User!')
            ->icon(RequestStatus::ACCEPTED->getIcon())
            ->iconColor(RequestStatus::ACCEPTED->getColor())
            ->body(str("<b>{$data['name']}</b> has been registered in the system")->toHtmlString())
            ->sendToDatabase(User::where('role', 'admin')->get());
    }
}
