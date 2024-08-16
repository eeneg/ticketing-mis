<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\Token;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as ContractsLogoutResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ContractsLogoutResponse::class, LogoutResponse::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Sanctum::usePersonalAccessTokenModel(Token::class);
        App::bind(LoginResponse::class, \App\Http\Responses\LoginResponse::class);
    }
}
