<?php

namespace App\Console\Commands;

use App\Enums\RequestStatus;
use App\Models\Request;
use Illuminate\Console\Command;

class Auto_Resolve extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto_-resolve';

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
        \App\Models\Request::query()
        ->whereHas('action', function($query){
           $query->where('status',RequestStatus::COMPLETED);
           $query->where('created_at','<=',now()->subHours(env('AUTO_RESOLVE_DURATION')));
        })
        ->with('action')
        ->lazy()
        ->each(function (Request $request){
            $request->actions->each(function($actions){
                $actions->update(['status'=>RequestStatus::RESOLVED]);
            });
        });
    }
}
