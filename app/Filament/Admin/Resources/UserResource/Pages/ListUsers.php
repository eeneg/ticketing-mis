<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'approved' => Tab::make('Approved User')
                ->modifyQueryUsing(fn ($query) => $query->whereNotNull('email_verified_at')),
            'pending' => Tab::make('Pending User')
                ->modifyQueryUsing(fn ($query) => $query->whereNull('email_verified_at')),
        ];
    }
}
