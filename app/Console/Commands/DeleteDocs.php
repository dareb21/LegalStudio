<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\jobDeleteDocs; // importa tu Job

class DeleteDocs extends Command
{
    protected $signature = 'delete:docs';
    public function handle()
    {
        jobDeleteDocs::dispatch(); // o dispatchSync() si quieres ejecución inmediata

        $this->info('MiJob ha sido ejecutado.');
    }
}
