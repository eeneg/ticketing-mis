<?php

namespace App\Filament\User\Resources\RequestResource\Pages;

use App\Filament\User\Resources\RequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequests extends ListRecords
{
    protected static string $resource = RequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
