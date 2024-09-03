<?php

namespace App\Filament\Admin\Resources\RequestResource\Pages;

use App\Enums\UserAssignmentResponse;
use App\Filament\Admin\Resources\RequestResource;
use App\Filament\Widgets\AdminRequestOverview;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

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

    public function getTabs(): array
    {
        return [
            Tab::make('All Requests')
                ->label('All Requests')
                ->modifyQueryUsing(fn (Builder $query) => $query),
            Tab::make('Accepted')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('assignees', fn (Builder $query) => $query->where('assignees.response', UserAssignmentResponse::ACCEPTED))),
            Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('assignees', fn (Builder $query) => $query->where('assignees.response', UserAssignmentResponse::PENDING))),
            Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('assignees', fn (Builder $query) => $query->where('assignees.response', UserAssignmentResponse::REJECTED))),
            Tab::make('Accepted')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('assignees', fn (Builder $query) => $query->where('assignees.response', UserAssignmentResponse::COMPLETED))),        ];
    }
}
