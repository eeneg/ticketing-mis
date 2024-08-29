<?php

namespace App\Filament\Officer\Resources\RequestResource\Pages;

use App\Filament\Officer\Resources\RequestResource;
use App\Filament\Widgets\OfficerRequestOverview;
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

    protected function getHeaderWidgets(): array
    {
        return
        [
            OfficerRequestOverview::class,
        ];
    }
}
