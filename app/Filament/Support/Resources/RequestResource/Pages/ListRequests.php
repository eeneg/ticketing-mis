<?php

namespace App\Filament\Support\Resources\RequestResource\Pages;

use App\Filament\Support\Resources\RequestResource;
use App\Filament\Widgets\SupportRequestOverview;
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
        return
        [
            SupportRequestOverview::class,
        ];
    }
    // https://www.youtube.com/watch?v=ma5UIuCiJ_I

    // public function getTabs(): array
    // {

    //     return [
    //         null => Tab::make('All'),
    //     ];
    // }
}
