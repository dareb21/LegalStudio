<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DownloadRequest;
use App\Models\Document;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $status = $request->reply ? "aprobada":"rechazada";

        $thisRequest->update([
            "status"=>$status,
            "responded_by"=>2, 
            "responseDate"=>$this->now
        ]);

        //Log::info(Auth::user()->name ." dio como  ". $status ." la solicitud. " . $this->now);  
        
    return response()->json("Solicitud ".$status);
    }

    public function deleteDoc($thisDoc)
    {
        $doc= Document::find($thisDoc)->delete();
        //Log::info(Auth::user()->name ." borro el archivo  ". $doc->documentName ." a las: " . $this->now);      
    
    return response()->json("El archivo se mando a la bandeja de reciclaje");
    }

    public function deleteDir($thisDir)
    {
        $dir =Folder::find($thisDir);
        $dir->delete();
        //Log::info(Auth::user()->name ." borro la carpeta  ". $dir->folderName ." a las: " . $this->now);      
    return response()->json("La carpeta se mando a la bandeja de reciclaje");
    }

    public function recycleCan()
    {
        $documents =Document::onlyTrashed()->where("hardDelete",null)->paginate(10);
        $folder =Folder::onlyTrashed()->where("hardDelete",null)->paginate(10);

    return response()->json([
            "documents"=>$documents,
            "folders"=>$folder,
        ]);
    }

    public function restoreDoc($thisDoc)
    {
        $folder = Document::withTrashed()->find($thisDoc);
        $folder->restore();
        //Log::info(Auth::user()->name ." restauro el documento ".  $folder->folderName ." a las " . $this->now);   
    return response()->json("Archivo restaurado");
    }

    public function restoreDir($thisDir)
    {
        $folder=Folder::withTrashed()->find($thisDir);
        $folder->restore();  
        //Log::info(Auth::user()->name ." restauro la carpeta ".  $folder->folderName ." a las " . $this->now);   
    return response()->json("Carpeta restaurada");
    }

    public function finishThisCase(Folder $thisDir)
    {
        $thisDir->update([
            "type"=>"finished"
        ]);
        //Log::info(Auth::user()->name ." cerrô el caso ".  $thisDir->folderName ." a las " . $this->now);   
    return response()->json("Su caso paso a cerrado");
    }

}
