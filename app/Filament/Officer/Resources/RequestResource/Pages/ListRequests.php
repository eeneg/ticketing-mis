<?php

namespace App\Filament\Officer\Resources\RequestResource\Pages;

use App\Filament\Officer\Resources\RequestResource;
use Filament\Resources\Pages\ListRecords;

class ListRequests extends ListRecords
{
    protected static string $resource = RequestResource::class;

    public $localResponse = null;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
