<?php

namespace App\Filament\Widgets;

use App\Enums\RequestStatus;
use App\Models\Assignee;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OfficerRequestOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Active Request', Assignee::where('assigner_id', Auth::id())->where('response', 'accepted')->count())
                ->descriptionIcon(RequestStatus::ACCEPTED->getIcon(), IconPosition::Before)
                ->color(RequestStatus::ACCEPTED->getColor())
                ->description('Requests that has been accepted'),
            Stat::make('Completed Request', Assignee::where('assigner_id', Auth::id())->where('response', 'completed')->count())
                ->descriptionIcon(RequestStatus::COMPLETED->getIcon(), IconPosition::Before)
                ->color('info')
                ->description('Requests that have been completed'),
            Stat::make('Pending Request', Assignee::where('assigner_id', Auth::id())->where('response', 'pending')->count())
                ->descriptionIcon(RequestStatus::PUBLISHED->getIcon(), IconPosition::Before)
                ->color('warning')
                ->description('Requests that needs to be accepted'),
        ];
    }
}
