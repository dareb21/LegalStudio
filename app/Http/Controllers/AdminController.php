<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DownloadRequest;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function showUsers()
{
    return response()->json(User::paginate(10));
}
public function banThisUser($userId)
{
 User::where("id", $userId)->update(["banned" => 1]);
return response()->json("Usuario bloqueado con exito");
}

public function unBanThisUser($userId)
{
 User::where("id", $userId)->update(["banned" => 0]);
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
return DownloadRequest::join("documents","download_requests.document_id","=","documents.id")
        ->join("users","download_requests.requested_by","=","users.id")
        ->select("download_requests.id","documents.id","documents.documentName","users.name","download_requests.requestDate")
        ->paginate(10);
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
return response()->json("Solicitud ".$status);
}



public function deleteDoc($thisDoc)
{
    Document::find($thisDoc)->delete();
    return response()->json("El archivo se mando a la bandera de reciclaje");
}

public function recycleCan()
{
   return Document::onlyTrashed()->paginate(10);
}

public function restoreDoc($thisDoc)
{
Document::withTrashed()->find($thisDoc)->restore();
return response()->json("Archivo restaurado");
}
}
