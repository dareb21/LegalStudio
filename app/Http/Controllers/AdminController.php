<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DownloadRequest;
use App\Models\Document;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function showUsers()
{
    return response()->json(User::paginate(10));
}
public function banThisUser($userId)
{
    $now= now()->setTimezone('America/Tegucigalpa')->format('Y-m-d H:i:s');
 $user = User::select("name")->where("id",$userId)->first();   
User::where("id", $userId)->update(["banned" => 1]);
Log::info(Auth::user()->name ." bloqueo a ". $user->name ." a las " . now()->format('H:i d/m/Y'));    
return response()->json("Usuario bloqueado con exito");
}

public function unBanThisUser($userId)
{
 $user = User::select("name")->where("id",$userId)->first();
 User::where("id", $userId)->update(["banned" => 0]);
Log::info(Auth::user()->name ." desbloqueo a ". $user->name ." a las " . now()->format('H:i d/m/Y'));  
return response()->json("Usuario desbloqueado con exito");
}

public function newUser(Request $request)
{
    $request->validate([
        "name" => "required|string|min:5",
        "birthday" => "required|date|before:today",
        "email" => "required|email",
        "phone" => "required|size:8",
        "role" => "required|in:abogado,asistente", 
    ]);

    try {
        User::create([
            "name"=> $request->name,
            "birthday"=>$request->birthday,
            "email"=>$request->email,
            "phone"=>$request->phone,
            "role"=>$request->role,
        ]);
    Log::info(Auth::user()->name ." creo un nuevo usuario bajo el nombre de  ". $request->name ."y le asigno el rol de ". $request->role. ". " . now()->format('H:i d/m/Y'));      
       return response()->json("Usuario creado con exito!"); 
    } catch (Exception $e) {
        return response()->json("Ha ocurrido en error al crear el usuario, intente nuevamente.");
    }
}

public function editUser(Request $request,$userId)
{

}

public function seeRequest()
{

 $petition =  DownloadRequest::join("documents","download_requests.document_id","=","documents.id")
        ->join("users","download_requests.requested_by","=","users.id")
        ->select("download_requests.id as requestId","documents.id as docId","documents.documentName as docName","users.name as userName","download_requests.requestDate as dateRequest")
        ->paginate(10);
return response()->json($petition);
}

public function replyRequest(DownloadRequest $thisRequest, Request $request)
{
$request->validate([
    "reply"=>"required|boolean"
]);

$thisRequest->update([
    "status"=>$request->reply,
    "responded_by"=>Auth::id(),
    "responseDate"=>now()
]);
$status = $request->reply ? "aprobada":"rechazada";

Log::info(Auth::user()->name ." dio como  ". $status ." la solicitud. " . now()->format('H:i d/m/Y'));  
return response()->json("Solicitud ".$status);
}



public function deleteDoc($thisDoc)
{
   $doc= Document::find($thisDoc)->delete();
Log::info(Auth::user()->name ." borro el archivo  ". $doc->documentName ." a las: " . now()->format('H:i d/m/Y'));      
    return response()->json("El archivo se mando a la bandeja de reciclaje");
}

 public function deleteDir($thisDir)
    {
        $dir =Folder::find($thisDir);
        $dir->delete();
        Log::info(Auth::user()->name ." borro la carpeta  ". $dir->folderName ." a las: " . now()->format('H:i d/m/Y'));      
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
Log::info(Auth::user()->name ." restauro el documento ".  $folder->folderName ." a las " . now()->format('H:i d/m/Y'));   
return response()->json("Archivo restaurado");
}

public function restoreDir($thisDir)
{
$folder=Folder::withTrashed()->find($thisDir);
 $folder->restore();  

Log::info(Auth::user()->name ." restauro la carpeta ".  $folder->folderName ." a las " . now()->format('H:i d/m/Y'));   
return response()->json("Carpeta restaurada");
}

public function finishThisCase(Folder $thisDir)
{
    $thisDir->update([
        "type"=>"finished"
    ]);
 
Log::info(Auth::user()->name ." cerrô el caso ".  $thisDir->folderName ." a las " . now()->format('H:i d/m/Y'));   
   
    return response()->json("Su caso paso a cerrado");
}

}
