<?php

namespace App\Http\Responses;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    protected User $user;

    public function toResponse($request): RedirectResponse|Redirector
    {
        $this->user = $request->user();

        $route = match ($this->user->role) {
            UserRole::ADMIN => 'filament.admin.resources.categories.index',
            UserRole::USER => 'filament.user.resources.requests.index',
            UserRole::OFFICER => 'filament.officer.resources.requests.index',
            UserRole::SUPPORT => 'filament.support.resources.requests.index',
        };

        return redirect()->route($route);
    }
}
