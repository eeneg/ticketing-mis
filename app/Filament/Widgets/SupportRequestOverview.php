<?php

namespace App\Filament\Widgets;

use App\Enums\RequestStatus;
use App\Models\Action;
use App\Models\Assignee;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SupportRequestOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            //Assignee::where('assigner_id', Auth::id())->where('response', 'accepted')->count()
            Stat::make('Pending Request', Assignee::where('user_id', Auth::id())->where('response', 'pending')->count())
                ->descriptionIcon(RequestStatus::PUBLISHED->getIcon(), IconPosition::Before)
                ->color('warning')
                ->description('Requests that has not yet been accepted'),
            Stat::make('Accepted Request', Assignee::where('user_id', Auth::id())->where('response', 'accepted')->count())
                ->descriptionIcon(RequestStatus::ACCEPTED->getIcon(), IconPosition::Before)
                ->color(RequestStatus::ACCEPTED->getColor())
                ->description('Requests that has been accepted'),
            Stat::make('Completed Request', Action::where('user_id', Auth::id())->where('status', RequestStatus::RESOLVED)->count())
                ->descriptionIcon(RequestStatus::COMPLETED->getIcon(), IconPosition::Before)
                ->color('info')
                ->description('Requests that have been'),
        ];
    }

    public function test() {}
}
