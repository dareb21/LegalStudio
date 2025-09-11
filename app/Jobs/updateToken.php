<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class jobDeleteFolders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function handle()
    {

$now = now()->setTimezone('America/Tegucigalpa'); 
$limit = $now->copy()->subMinutes(15);           

PersonalAccessToken::where('last_used_at', '>=', $limit->toDateTimeString())->update([
        'last_used_at' => \DB::raw('DATE_ADD(last_used_at, INTERVAL 15 MINUTE)')
    ]);

    }
}
