<?php

namespace App\Filament\Admin\Resources\RequestResource\Pages;

use App\Filament\Admin\Resources\RequestResource;
use App\Filament\Widgets\AdminRequestOverview;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;

class ListRequests extends ListRecords
{
    protected static string $resource = RequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdminRequestOverview::class,
        ];
    }

}
