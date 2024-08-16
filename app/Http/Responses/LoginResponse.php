<?php

namespace App\Http\Responses;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    protected User $user;

    public function toResponse($request): RedirectResponse|Redirector
    {
        $this->user = $request->user();

        $route = match ($this->user->role) {
            UserRole::ADMIN => 'filament.admin.pages.dashboard',
            UserRole::USER => 'filament.user.resources.requests.index',
            UserRole::OFFICER => 'filament.officer.resources.requests.index',
            UserRole::SUPPORT => 'filament.support.resources.requests.index',
            default=> null
        };

        if($route==null)
        {
            Notification::make()
                ->title('You have no roles!')
                ->warning()
                ->send();
                Auth::logout();

                $route='filament.user.auth.login';
        }

        return redirect()->route($route);
    }
}
