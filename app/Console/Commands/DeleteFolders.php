<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\jobDeleteFolders; // importa tu Job

class DeleteFolders extends Command
{
    protected $signature = 'delete:dirs';
    public function handle()
    {
        jobDeleteFolders::dispatch(); // o dispatchSync() si quieres ejecución inmediata
    }
}
