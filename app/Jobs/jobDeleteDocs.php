<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class jobDeleteDocs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {    Log::info('Entró al handle del Job');
        Document::withTrashed()
            ->whereNotNull("hardDelete")
            ->select("folderPath","documentName")
            ->chunk(200, function ($documents) {
                foreach ($documents as $doc) {
                    Storage::disk('private')->delete($doc->folderPath."/".$doc->documentName);
                }
            });
    }
}
