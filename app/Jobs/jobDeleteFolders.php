<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class jobDeleteFolders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function handle()
    {
        Folder::withTrashed()
            ->whereNotNull("hardDelete")
            ->select("folderPath","id")
            ->chunk(200, function ($folders) {
                foreach ($folders as $dir) {
                    if(is_null($dir->folderPath))
                    {
                        $path= "/".$dir->id;
                    }else{
                        $path=$dir->folderPath;
                    }
                    Log::info("El path es ".$path);
                    Storage::disk('private')->delete($path);
                }
            });
    }
}
