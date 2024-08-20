<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\Token;
use Filament\Forms\Components\Select;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as ContractsLogoutResponse;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Assets\Css;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
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

        FilamentAsset::register([Css::make('app', __DIR__.'/../../resources/css/app.css')]);

        Select::configureUsing(fn (Select $select) => $select->native(false));

        Table::configureUsing(fn (Table $table) => $table->paginated([10, 25, 50, 100])->defaultPaginationPageOption(25)->striped());

        Notifications::verticalAlignment(VerticalAlignment::End);

        Notifications::alignment(Alignment::Start);
    }
}
