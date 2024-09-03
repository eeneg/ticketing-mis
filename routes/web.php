<?php

use App\Enums\RequestStatus;
use App\Enums\UserAssignmentResponse;
use App\Models\Request;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test', function () {
    // $time=now()->addHour();

    // return $record = App\Models\Request::query()
    //     ->whereDate('target_date',$time)
    //     ->whereTime('target_time', $time)
    //     ->whereHas('assignees', function ($querry){
    //         $querry->where('response', UserAssignmentResponse::ACCEPTED);
    //     })
    //     ->whereDoesntHave('actions',function ($querry){
    //         $querry->where('status' , RequestStatus::STARTED);
    //     })
    //     ->with('assignees')
    //     ->lazy()
    //     ->each(function (Request $request) {

    //         Notification::make()
    //             ->title('test')
    //             ->sendToDatabase($request->assignees);

    //     });
    $time = now()->addHour();

    return \App\Models\Request::query()
        ->whereDate('target_date', '2024-09-02')
        ->whereTime('target_time', '11:58')
        // ->whereHas('assignees', function ($querry){
        //     $querry->where('response', UserAssignmentResponse::ACCEPTED);
        // })
        // ->whereDoesntHave('actions',function ($querry){
        //     $querry->where('status' , RequestStatus::STARTED);
        // })
        ->with('assignees')
        ->with('requestor')
        ->with('office')
        ->lazy()
        ->each(function (Request $request) {
            // dd($request);
            dd(str("Request “<i>{$request->subject}</i>” by <b>{$request->requestor->name}</b> at <b>{$request->office->acronym}</b> will start in <b>1 hour</b>")->toHtmlString());
            // Notification::make()
            //     ->title('Upcoming Request');
            // ->sendToDatabase($request->assignees);

        });
});
