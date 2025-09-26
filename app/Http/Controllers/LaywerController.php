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
$user = $request->user();  
        $thisRequest->update([    // Esto se puede hacer en una sola consulta
            "status"=>$request->reply,
            "responded_by"=>$user->id, 
            "responseDate"=>$this->now
        ]);
      $status = $request->reply ? "aprobò":"rechazò";
   Logger::create([
    "who" => $user->id,
    "details" => $user->name . $status  . "la solicitud de descarga de: " . $thisRequest->requested_by_name . " con fin de descargar el documento ".$thisRequest->document_name . " el dia " . $this->now,
]);
    return response()->json([
        "statusP" => "Su solicitud fue ". $status,
    ]);
    }

    public function deleteDoc(Document $thisDoc, Request $request)
    {;
        $user = $request->user();  
        Document::where("id",$thisDoc->id)->update([
            "deleted_by"=>$user->id,
            "deleted_at"=> $this->now,
            "deleted_by_name"=>$user->name,
        ]);
        $docName = $thisDoc->documentName; 
        
   Logger::create([
    "who" => $user->id,
    "doc"=>$thisDoc->id,
    "details" => $user->name ." elimino el documento ". $docName  .  " el dia " . $this->now,
]);
   
        return response()->json("El archivo se mando a la bandeja de reciclaje");
    }

    public function deleteDir(Folder $thisDir, Request $request)
    {
        $user = $request->user();  
        $dirName = $thisDir->folderName;
        Folder::where("id",$thisDir->id)->update([
            "deleted_by"=>$user->id,
            "deleted_at"=>$this->now,
            "deleted_by_name"=>$user->name,
        ]);

Logger::create([
    "who" => $user->id,
    "details" => $user->name." elimino la carpeta ". $dirName  .  " el dia " . $this->now,
]);
         
    return response()->json("La carpeta se mando a la bandeja de reciclaje");
    }

    public function recycleCan($dirType)
        {
        if (!in_array($dirType,['active', 'finished', 'jurisprudence'])){
            return response()->json("Tipo de carpeta no valida");
        }  
        
        $documents =Document::onlyTrashed()
          ->join("folders","documents.folder_id","=","folders.id")
          ->whereNull("folders.hardDelete")
          ->whereNull("documents.hardDelete")
          ->where("folders.type",$dirType)
          ->orderBy("documents.deleted_at","desc")
          ->select("documents.id as docId","documents.documentName as docName","documents.description as docDesc","documents.whoMadeIt as whoUpload","documents.isSensitive","documents.deleted_at as deletedAt","documents.important","documents.judge","documents.deleted_by_name as deletedBy")
          ->paginate(10);

        $folder =Folder::onlyTrashed()
        ->where("type",$dirType)
        ->where("hardDelete",null)
        ->orderBy("deleted_at","desc")
        ->paginate(10);

    return response()->json([
            "documents"=>$documents,
            "folders"=>$folder,
        ]);
    }

    public function restoreDoc($thisDoc, Request $request) 
    { 
        $user = $request->user();
        $doc = Document::withTrashed()->find($thisDoc); //esto es otro update solo que con trashed
        $docName = $doc->documentName;
        $doc->deleted_by = null;
        $doc->deleted_at=null;
        $doc->deleted_by_name=null;
        $doc->save();
Logger::create([
    "who" => $user->id,
    "doc"=>$thisDoc,
    "details" => $user->name . " restauro el documento ". $docName  .  " el dia " . $this->now,
]);
    return response()->json("Archivo restaurado");
    }

    public function restoreDir($thisDir, Request $request)
    {
        $user = $request->user();
        $folder=Folder::withTrashed()->find($thisDir);
        $folderName = $folder->folderName;
        $folder->deleted_at = null;
        $folder->deleted_by = null;
        $folder->deleted_by_name=null;
        $folder->save();
     
   Logger::create([
    "who" => $user->id,
    "details" => $user->name . " restauro la carpeta ". $folderName  .  " el dia " . $this->now,
]); return response()->json("Carpeta restaurada");
    }

    public function finishThisCase(Folder $thisDir, Request $request)
{
$user = $request->user();
    $data = [];
    $toDeleteInfo = [];
    $toSave = [];

    $toDelete = $request->toDelete;
   
    $toDeleteInfo["deleted_at"] = $this->now;
    $toDeleteInfo["deleted_by"] =$user->id;
    $toDeleteInfo["deleted_by_name"] =$user->name;
    

    DB::beginTransaction();
    try {
      
if ($thisDir->folderPath == null)
{
        $thisDir->update([
            "type" => "finished",
        ]);

}else{

      $oldPath = $thisDir->folderPath;
        $newPath = (string) $thisDir->id; 
        $disk = Storage::disk('estudioLegal');
        $disk->makeDirectory(dirname($newPath));
        rename(
            $disk->path($oldPath),  
            $disk->path($newPath)   
        );

        $folders = Folder::select("folderPath","id")->where('folderPath', 'like', '%/'.$thisDir->id.'%')->get();

        $thisDir->update([
            "type" => "finished",
            "folderPath" => null,
            "parentFolder"=>null,
        ]);

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
                    END,
                    type = 'finished'
                    WHERE id IN ($idsStr)
                    AND id != {$thisDir->id}
                ");

            DB::update("
                UPDATE documents 
                SET folderPath = CASE folder_id 
                    $cases 
                END
                WHERE folder_id IN ($idsStr)
            ");
        }
}
        Document::whereIn("id",$toDelete)->update($toDeleteInfo);

       Logger::create([
            "who" => $user->id,
            "details" => $user->name ." Cerro el caso ". $thisDir->folderName  .  " el dia " . $this->now,
        ]);  

        DB::commit(); 
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Error al guardar el documento', 'detalle' => $e->getMessage()], 500);
    }

    return response()->json("Su caso paso a cerrado");
}


public function quickLogs()
{
 $fifteenDays = \Carbon\Carbon::parse($this->now)->subDays(15);
    $logs = Logger::where('created_at', '>=', $fifteenDays)
                   ->select("details") 
                  ->orderBy('created_at', 'desc')
                  ->paginate(30);    
   return response()->json([
        "logs" => $logs
    ]);
}

public function updateDir($thisDir, Request $request)
{
     $request->validate([
           "folderName"=>"required|string|filled"
        ]);
    $user = $request->user(); 
    Folder::where("id",$thisDir)->update([
    "folderName"=>$request->folderName,
    ]);

    Logger::create([
    "who" => $user->id,
    "details" =>$user->name." modifico el nombre de la carpeta: ".$request->folderName ." el dia " . $this->now,
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
    $array=[
        "documentName" =>"Nombre documento",
        "isSensitive"=>"Sensibilidad de documento",
        "description"=>"Descripcion de documento",
        "important"=>"Importancia de caso",
        "judge"=>"Nombre de Juez"
    ];
 $doc= Document::where("id",$thisDoc)->first()->toArray();   
 $user = $request->user();
 
$changes = array_intersect_key($array, $validated);
$oldData = array_intersect_key($doc, $changes);
$string = "";
foreach ($changes as $field => $label) {
    $old = $oldData[$field] ?? 'N/A';          
    $new = $request[$field] ?? 'N/A';     
    $string .= $label." De:". $old. " A: ".$new. " ";
}

Document::where('id', $thisDoc)->update($validated);
Logger::create([
    "who" => $user->id,
    "doc"=>$thisDoc,
    "details" =>$user->name." modifico los campos: ". $string  ." del documento: ".$doc["documentName"] ." el dia " . $this->now,
]);
return response()->noContent();
}

public function docActivity($thisDoc)
{
$logs = Logger::select("details")->where("doc",$thisDoc)->orderBy("created_at","asc")->paginate(50);
return response([
    "logs"=>$logs
]);
}

public function downloadDoc($thisDoc, Request $request)
{
 $docInfo=Document::where("id",$thisDoc)->select("documentName","folderPath")->first();
 if (!$docInfo)
 {
    return response()->json("No se encontro este archivo.");
 }
 $path =ltrim($docInfo->folderPath . "/" . $docInfo->documentName);

 $user = $request->user(); 
  
    Logger::create([
    "who" => $user->id,
    "doc"=>$thisDoc,
    "details" => $user->name." descargo el documento: " . $docInfo->documentName . " el dia " . $this->now,
]);    
    return Storage::disk("estudioLegal")->download($path);
    }

}