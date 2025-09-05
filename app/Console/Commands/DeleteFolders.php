<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\jobDeleteFolders; 

class DeleteFolders extends Command
{
    protected $signature = 'delete:dirs';
    public function handle()
    {
        jobDeleteFolders::dispatch();
          $this->info('MiJob ha sido ejecutado.');
    }
}
