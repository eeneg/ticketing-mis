<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\Token;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as ContractsLogoutResponse;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
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
        Sanctum::usePersonalAccessTokenModel(Token::class);
        FilamentAsset::register([
            Css::make('app', __DIR__.'/../../resources/css/app.css'),
        ]);
    }
}
