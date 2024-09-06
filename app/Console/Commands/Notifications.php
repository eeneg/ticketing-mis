<?php

namespace App\Console\Commands;

use App\Enums\RequestStatus;
use App\Enums\UserAssignmentResponse;
use App\Models\Request;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class Notifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $time = now('H:i:00')->addHour();

        \App\Models\Request::query()
            ->whereDate('target_date', now())
            ->whereTime('target_time', $time)
            ->whereHas('assignees', function ($querry) {
                $querry->where('response', UserAssignmentResponse::ACCEPTED);
            })
            ->whereDoesntHave('actions', function ($querry) {
                $querry->where('status', RequestStatus::STARTED);
            })
            ->with('assignees')
            ->lazy()
            ->each(function (Request $request) {

                Notification::make()
                    ->title('Request due in 1 Hour!')
                    ->icon(RequestStatus::SCHEDULED->getIcon())
                    ->iconColor(RequestStatus::SCHEDULED->getColor())
                    ->body(str("Request “<i>{$request->subject}</i>” by <b>{$request->requestor->name}</b> at <b>{$request->office->acronym}</b> will start in <b>1 hour</b>")->toHtmlString())
                    ->sendToDatabase($request->assignees);

            });
    }
}
