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
        Folder::onlyTrashed()
            ->whereNotNull("hardDelete")
            ->select("folderPath","id")
            ->chunk(200, function ($folders) {
                foreach ($folders as $dir) {
                  $path = ltrim($dir->folderPath ?? $dir->id, '/'); // quita el "/" inicial
Log::info("Intentando borrar: " . Storage::disk('private')->path($path));
Storage::disk('private')->deleteDirectory($path);

                }
            });
    }
}
