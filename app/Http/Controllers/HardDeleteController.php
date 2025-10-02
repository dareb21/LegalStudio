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
$disk = Storage::disk("private");
Document::onlyTrashed()
    ->whereNotNull("hardDelete")
    ->select("folderPath", "id")
    ->chunk(200, function ($docs) use ($disk,&$path) {
        foreach ($docs as $doc) {
            $path[] = $doc->folderPath . "/docId_" . $doc->id;
        }
    });
 $disk->delete($path);
return response()->json("Borrados");
}

public function deleteFolders()
{
    $ids = [];
$disk = Storage::disk("private");
Folder::onlyTrashed()
    ->where("hardDeleted",false)
    ->select("folderPath","id")
      ->chunk(200, function ($dirs) use ($disk, &$ids)  {
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
Folder::whereIn("id",$ids)->update("hardDeleted",true);    
}
return response()->json("Borrado con exito");
}
}
