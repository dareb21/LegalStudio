<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\updateToken; 

class UpdateTokens extends Command
{
    protected $signature = 'update:tokens';
    public function handle()
    {
        updateToken::dispatch();
          $this->info('MiJob ha sido ejecutado.');
    }
}
