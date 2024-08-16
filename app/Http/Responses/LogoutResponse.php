<?php

namespace App\Http\Responses;

use App\Models\User;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LogoutResponse implements Responsable
{
    protected User $user;

    public function toResponse($request): RedirectResponse|Redirector
    {
        // $request->session_id()->flush();

        return redirect()->route('filament.user.auth.login');
    }
}
