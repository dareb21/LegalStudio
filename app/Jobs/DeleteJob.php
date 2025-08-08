<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;
class DeleteJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
  
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
  $dirs = Folder::withTrashed()->whereNotNull("hardDelete")->select("folderPath","id")->get();
    
   foreach ($dirs as $dir)
   {
    if(is_null($dir->folderPath))
    {    
       Storage::disk("public")->deleteDirectory($dir->id);
    }
    else
    {
         Storage::disk("public")->deleteDirectory($dir->folderPath); 
    }
   }
   
    }
}