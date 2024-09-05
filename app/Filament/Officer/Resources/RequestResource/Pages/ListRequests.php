<?php

namespace App\Filament\Officer\Resources\RequestResource\Pages;

use App\Enums\RequestStatus;
use App\Filament\Officer\Resources\RequestResource;
use App\Filament\Widgets\OfficerRequestOverview;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

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

    public function getTabs(): array
    {

        return [

            Tab::make('All Requests')
                ->label('All Requests')
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query),
            Tab::make('publised')
                ->label('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', function (Builder $query) {
                    $query->where('status', RequestStatus::PUBLISHED);
                })),
            Tab::make('assigned')
                ->label('Assigned')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', function (Builder $query) {
                    $query->where('status', RequestStatus::ASSIGNED);
                })),
            Tab::make('approved')
                ->label('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', function (Builder $query) {
                    $query->where('status', RequestStatus::APPROVED);
                })),
            Tab::make('started')
                ->label('Started')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', function (Builder $query) {
                    $query->where('status', RequestStatus::STARTED);
                })),
            Tab::make('resolved')
                ->label('Resolved')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', function (Builder $query) {
                    $query->where('status', RequestStatus::RESOLVED);
                })),
        ];
    }
}
