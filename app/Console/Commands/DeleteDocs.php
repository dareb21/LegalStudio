<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\jobDeleteDocs; 
class DeleteDocs extends Command
{
    protected $signature = 'delete:docs';
    public function handle()
    {
        jobDeleteDocs::dispatch(); 

        $this->info('MiJob ha sido ejecutado.');
    }
}
