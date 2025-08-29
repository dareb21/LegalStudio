<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DownloadRequest;
use App\Models\Document;
use App\Models\Folder;
use App\Models\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LaywerController extends Controller
{
  private $now;

    public function __construct()
    {
        $this->now = now()->setTimezone('America/Tegucigalpa')->format('Y-m-d H:i:s');
    }

   public function seeRequest()
    {
      $petition =  DownloadRequest::join("documents","download_requests.document_id","=","documents.id")
         ->join("users","download_requests.requested_by","=","users.id")
         ->select("download_requests.id as requestId","documents.id as docId","documents.documentName as docName","users.name as userName","download_requests.requestDate as dateRequest")
         ->where("download_requests.status",null)
         ->orderBy("download_requests.requestDate","desc")
         ->paginate(10);
    return response()->json([
    "petitions" =>   $petition
    ]);
    }

    public function replyRequest(DownloadRequest $thisRequest, Request $request)
    {
        $request->validate([
           "reply"=>"required|boolean"
        ]);
      
        $thisRequest->update([
            "status"=>$request->reply,
            "responded_by"=>2, 
            "responseDate"=>$this->now
        ]);
      $status = $request->reply ? "aprobo":"rechazo";
   Logger::create([
    "who" => 2,
    "details" => "Pedro Garcia ". $status  . "la solicitud numero" . $thisRequest->id . " a las " . $this->now,
]);
        
    return response()->json([
        "statusP" => "Su solicitud fue ". $status,
    ]);
    }

    public function deleteDoc($thisDoc)
    {
        $doc= Document::findOrFail($thisDoc);
        $doc->deleted_by = 1; //Auth::user()->name;
        $docName = $doc->documentName; 
        $doc->save(); 
        $doc->delete();
   Logger::create([
    "who" => 1,
    "details" => "Carlos Palma elimino el documento ". $docName  .  " a las " . $this->now,
]);
   
        return response()->json("El archivo se mando a la bandeja de reciclaje");
    }

    public function deleteDir($thisDir)
    {
        $dir =Folder::findOrFail($thisDir);
        //$dir->deleted_by = 1; //Auth::user()->name;
        $dirName = $dir->folderName;
        $dir->save();
        $dir->delete();
Logger::create([
    "who" => 1,
    "details" => "Carlos Palma elimino la carpeta ". $dirName  .  " a las " . $this->now,
]);
         
    return response()->json("La carpeta se mando a la bandeja de reciclaje");
    }

    public function recycleCan($dirType)
        {
          $documents =Document::onlyTrashed()
          ->join("folders","documents.folder_id","=","folders.id")
          ->whereNull("folders.hardDelete")
          ->where("folders.type",$dirType)
          ->select("documents.id as docId","documents.documentName as docName","documents.description as docDesc","documents.whoMadeIt as whoUpload","documents.isSensitive","documents.deleted_at as deletedAt","documents.important","documents.judge")
          ->paginate(10);  
        $folder =Folder::onlyTrashed()->where("type",$dirType)->where("hardDelete",null)->paginate(10);

    return response()->json([
            "documents"=>$documents,
            "folders"=>$folder,
        ]);
    }

    public function restoreDoc($thisDoc)
    {
        $doc = Document::withTrashed()->find($thisDoc);
        $docName = $doc->documentName;
        $doc->deleted_by = null;
        $doc->save();
        $doc->restore();
Logger::create([
    "who" => 1,
    "details" => "Carlos restauro el documento ". $docName  .  " a las " . $this->now,
]);
    return response()->json("Archivo restaurado");
    }

    public function restoreDir($thisDir)
    {
        $folder=Folder::withTrashed()->find($thisDir);
        $folderName = $folder->folderName;
        $folder->deleted_by = null;
        $folder->save();
        $folder->restore();  
   Logger::create([
    "who" => 1,
    "details" => "Carlos restauro la carpeta ". $folderName  .  " a las " . $this->now,
]); return response()->json("Carpeta restaurada");
    }

    public function finishThisCase(Folder $thisDir, Request $request)
{
    $data = [];
    $toDeleteInfo = [];
    $toSave = [];

    $toDelete = $request->toDelete;
    $toSave = $request->toSave;

    $toDeleteInfo["deleted_at"] = $this->now;
    $toDeleteInfo["deleted_by"] = 1;

    DB::beginTransaction();
    try {
        $oldPath = $thisDir->folderPath;
        $newPath = (string) $thisDir->id; 

        $disk = Storage::disk('private');
        $disk->makeDirectory(dirname($newPath));
        rename(
            $disk->path($oldPath),  
            $disk->path($newPath)   
        );

        $thisDir->update([
            "type" => "finished",
            "folderPath" => null
        ]);

        $folders = Folder::select("folderPath","id")->where('folderPath', 'like', '%/'.$thisDir->id.'%')->get();

        $ids = [];
        $cases = "";
        foreach ($folders as $folder) {
            $newPath = substr($folder->folderPath, strpos($folder->folderPath, "/".$thisDir->id));        

            $cases .= " WHEN {$folder->id} THEN '{$newPath}'";
            $ids[] = $folder->id;
        }

        if (!empty($ids)) {
            $idsStr = implode(",", $ids);

            DB::update("
                UPDATE folders 
                SET folderPath = CASE id 
                    $cases 
                END
                WHERE id IN ($idsStr)
            ");

            DB::update("
                UPDATE documents 
                SET folderPath = CASE folder_id 
                    $cases 
                END
                WHERE folder_id IN ($idsStr)
            ");
        }

        Document::whereIn("id",$toDelete)->update($toDeleteInfo);

       Logger::create([
            "who" => 1,
            "details" => "Cerro el caso ". $thisDir->folderName  .  " a las " . $this->now,
        ]);  

        DB::commit(); 
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Error al guardar el documento', 'detalle' => $e->getMessage()], 500);
    }

    return response()->json("Su caso paso a cerrado");
}


public function logs()
{
    $logs = Logger::paginate(30);
    return response()->json([
        "logs" => $logs
    ]);
}

public function updateDir($thisDir, Request $request)
{
     $request->validate([
           "folderName"=>"required|string|filled"
        ]);
    Folder::where("id",$thisDir)->update([
    "folderName"=>$request->folderName,
    ]);
   return response()->noContent();
}

public function updateDoc($thisDoc, Request $request)
{
   $validated=  $request->validate([
        "documentName"=>"string|filled",
        "isSensitive"=>"boolean",
        "description"=>"string|filled",
        "important"=>"integer|in:1,2,3",
        "judge"=>"string|filled",
    ]);
Document::where('id', $thisDoc)->update($validated);
  return response()->noContent();
}


}
