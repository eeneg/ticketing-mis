<?php

namespace App\Filament\Support\Resources\RequestResource\Pages;

use App\Filament\Support\Resources\RequestResource;
use Filament\Resources\Pages\ListRecords;

class ListRequests extends ListRecords
{
    protected static string $resource = RequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
