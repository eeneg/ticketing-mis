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

    return \App\Models\User::query()
        ->where('role','support')
        ->with('office')
        ->get();
});
