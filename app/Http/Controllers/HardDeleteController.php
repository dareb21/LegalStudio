<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Models\Document;
use  App\Models\Folder;
use Illuminate\Support\Facades\Storage;

class HardDeleteController extends Controller
{
 public function deleteDocs()
    {
$path = [];      
$ids = [];  
$disk = Storage::disk("estudioLegal");
Document::onlyTrashed()
    ->where("hardDeleted",false)
    ->select("folderPath", "id")
    ->chunk(200, function ($docs) use (&$path,&$ids) {
        foreach ($docs as $doc) {
            $path[] = $doc->folderPath . "/docId_" . $doc->id;
          $ids[] =  $doc->id;
        }
    });
if (!empty($ids))
{
Document::withTrashed()->whereIn("id",$ids)->update(['hardDeleted' => True]);   
 $disk->delete($path);
}
}

public function deleteFolders()
{
$ids = [];
$disk = Storage::disk("estudioLegal");
Folder::onlyTrashed()
    ->where("hardDeleted",false)
    ->select("folderPath","id")
      ->chunk(200, function ($dirs) use (&$disk, &$ids)  {
        foreach ($dirs as $dir) {
            if (is_null($dir->folderPath))
            {
                $path= "/". $dir->id;
            }else{
                $path=$dir->folderPath;
            }
           $disk->deleteDirectory($path);
        $ids[] =  $dir->id;
        }
    });
    
if (!empty($ids))
{

Folder::withTrashed()->whereIn('id', $ids)->update(['hardDeleted' => True]); 
}
}
}
