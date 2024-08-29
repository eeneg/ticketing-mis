<?php

namespace App\Filament\Widgets;

use App\Enums\RequestStatus;
use App\Models\Assignee;
use App\Models\Request;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminRequestOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Unassigned Request',Request::doesntHave('assignees')->count())
                ->description('Request that has no assignee')
                ->descriptionIcon(RequestStatus::STARTED->getIcon(), IconPosition::Before),
            Stat::make('Active Request', Assignee::where('response','accepted')->count())
                ->descriptionIcon(RequestStatus::ACCEPTED->getIcon(), IconPosition::Before)
                ->color(RequestStatus::ACCEPTED->getColor())
                ->description('Request that has been accepted'),
            Stat::make('Completed Request', Assignee::where('response','completed')->count())
                ->descriptionIcon(RequestStatus::COMPLETED->getIcon(), IconPosition::Before)
                ->color('info')
                ->description('Request that needed to be accepted'),
            Stat::make('Pending Request', Assignee::where('response','pending')->count())
                ->descriptionIcon(RequestStatus::PUBLISHED->getIcon(), IconPosition::Before)
                ->color('warning')
                ->description('Request that has not yet accepted'),
        ];
    }

    public function test()
    {

    }

}
