<?php

namespace App\Filament\Support\Resources\RequestResource\Pages;

use App\Enums\RequestStatus;
use App\Enums\UserAssignmentResponse;
use App\Filament\Support\Resources\RequestResource;
use App\Filament\Widgets\SupportRequestOverview;
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
        return
        [
            SupportRequestOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            Tab::make('All Requests')
                ->label('All Requests')
                ->modifyQueryUsing(fn (Builder $query) => $query),
            Tab::make('accepted')
                ->label('Accepted')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('currentUserAssignee', fn (Builder $query) => $query->where('response', UserAssignmentResponse::ACCEPTED))),
            Tab::make('pending')
                ->label('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('currentUserAssignee', fn (Builder $query) => $query->where('response', UserAssignmentResponse::PENDING))),
            Tab::make('rejected')
                ->label('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('currentUserAssignee', fn (Builder $query) => $query->where('response', UserAssignmentResponse::REJECTED))),
            Tab::make('completed')
                ->label('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', function (Builder $query) {
                    $query->where('status', RequestStatus::RESOLVED);
                })),
        ];
    }

    public ?string $activeTab = '2';
}
