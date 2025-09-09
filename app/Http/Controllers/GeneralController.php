<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use  App\Models\Folder;
use App\Models\Document;
use App\Models\DownloadRequest;
use App\Jobs\DeleteJob;
use App\Models\Logger;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Sof;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


class GeneralController extends Controller
{
 private $now;

    public function __construct()
    {
        $this->now = now()->setTimezone('America/Tegucigalpa')->format('Y-m-d H:i:s');
    }

    public function dashboard()
    { 
     //DeleteJob::dispatch();   
     
    #Espacio Disponible, Espacio Total, % de espacio ocupado    
    $totalSpace = disk_total_space("/estudioLegal");
    $freeSpace = disk_free_space("/estudioLegal"); 

    $freeGB = round($freeSpace / 1024 / 1024 / 1024, 2); 
    $totalGB = round($totalSpace / 1024 / 1024 / 1024, 2);
    
    $usedGB = $totalGB - $freeGB;
    $porcentualUsedGB = round(($usedGB / $totalGB) * 100, 2);
    
    #Cuantos Docs hay 
    $docs = Document::count("id"); //indexar documente Id, creo que ya esta indexado


    //Logs ultimos 10
    //Peticiones de descarga pendientes
return [
        "totalSpace"=>$totalGB,
        "usedGb"=>$porcentualUsedGB,
        "howManyDocs"=>$docs,
        ];
    }

 public function showDirs($type)
    {
    if (!in_array($type, ['active', 'finished', 'jurisprudence'])) {
        return response()->json(['error' => 'Tipo de carpeta no reconocido'], );
    }    
    $dirs = Folder::select("id","folderName")->where("parentFolder",null)->where("type",$type)->OrderBy("important","asc")->OrderBy("created_at","desc")->get(); //Indexar parentFolder   
    return  response()->json($dirs);  
}

public function showThisDir($thisDir)
{
    $dirs = Folder::select("id","folderName","important")->where("parentFolder",$thisDir)->OrderBy("important","asc")->OrderBy("created_at","desc")->paginate(20);  //Indexar este campo
    return  response()->json([
        "dirs"=>$dirs,
    ]);
}

public function showDocs($thisDir)
{
    $docs = Document::where("folder_id",$thisDir)->OrderBy("important","asc")->OrderBy("created_at","desc")->paginate(20); //Indexar folder_id
    return  response()->json([
      "docs"=>  $docs
    ]);
}
    public function makeDir(Request $request)   
    {
        
       $request->validate([
        "parentFolder"=>"integer|min:1",
        "important"=>"required|integer|in:1,2,3",
        "folderName"=>"required|string|filled",
        "folderType"=>"required|string|in:active,finished,jurisprudence"
       ]); 
    $isRoot = false;
    $folderName = $request->input('folderName');
    $parentFolder = $request->input('parentFolder');
    $type =  $request->input('folderType');
     $important =  $request->input('important');
     if ($parentFolder == 0)
     {
        $parentFolder = null;
        $isRoot = true;
     }
     
    $newFolder= Folder::create([
        "folderName"=>$folderName ,
        "parentFolder" =>$parentFolder,
        "type"=>$type,
        "important"=> $important,
     ]);
     if ($isRoot)
     {
        Storage::disk('estudioLegal')->makeDirectory($newFolder->id);
        //Storage::disk('private')->makeDirectory($newFolder->id);
         
     }else
     {
$results = DB::select("
    WITH RECURSIVE folder_tree AS (
        SELECT id, folderName, parentFolder
        FROM folders
        WHERE id = ?

        UNION ALL

        SELECT f.id, f.folderName, f.parentFolder
        FROM folders f
        INNER JOIN folder_tree ft ON f.id = ft.parentFolder
    )
    SELECT parentFolder FROM folder_tree order by parentFolder ASC
", [$newFolder->id]); 
$path = "";
foreach ($results as $item)
{
   $path .= $item->parentFolder . '/';
}
$fullPath = $path. $newFolder->id;
$newFolder->folderPath=$fullPath;
$newFolder->save();
Storage::disk('estudioLegal')->makeDirectory($fullPath);
//Storage::disk('private')->makeDirectory($fullPath);
}
switch ($type) {
    case 'active':
        $logType = 'Casos activos';
        break;
    case 'finished':
        $logType = 'Casos finalizados';
        break;
    case 'jurisprudence':
        $logType = 'Jurisprudencia';
        break;
    default:
        $logType = 'Carpeta Desconocida';
}
 $user = $request->user(); 
Logger::create([
    "who" => $user->id,
    "details" => $user->name ." creo una carpeta llamada: " . $folderName . ", del tipo: " . $logType . " el dia " . $this->now,
]);
 return response()->json("Carpeta Creada con exito");
    }

   

public function uploadDoc(Request $request,$thisDir)
    { 
      $request->validate([
         "important"=>"required|integer|in:1,2,3",
         "description"=>"required|string|filled",
        "judge" => "nullable|string",
       "isSensitive" => "required|boolean",
         'file' => 'required|file|max:1992294',  
      ]); 
 DB::beginTransaction();
try { 
    $folder = Folder::select("folderPath","id","type")->where("id",$thisDir)->first();
    $file = $request->file('file');
    $fileName = $file->getClientOriginalName();

 if (!$folder)
    {
        DB::rollBack();
        return response()->json(['error' => 'Carpeta no encontrada'], 404);
    }

if (is_null($folder->folderPath))
    {
        $folderPath = "/". $folder->id;
    }else
    {
        $folderPath = $folder->folderPath;
    }
$file->storeAs($folderPath,$fileName,"estudioLegal");
//$file->storeAs($folderPath,$fileName,"private");
     $doc= Document::create([ 
          "documentName"   => $fileName,  
          "folder_id"      =>$folder->id,
          "folderPath" =>   $folderPath,
          "description"    => $request->description,
          "judge"          => $request->judge,
          "whoMadeIt"      => $user->name,
          "isSensitive"    => $request->isSensitive,
          "important" => $request->important
        ]);

switch ($folder->type) {
    case 'active':
        $logType = 'casos activos';
        break;
    case 'finished':
        $logType = 'casos finalizados';
        break;
    case 'jurisprudence':
        $logType = 'Jurisprudencia';
        break;
    default:
        $logType = 'Carpeta Desconocida';
}

 $user = $request->user(); 
Logger::create([
    "who" => $user->id,
    "doc"=> $doc->id,
    "details" => $user->name . " subio el documento: " . $fileName . " el dia " . $this->now." en el area: " . $logType,
]);
        DB::commit();
    
             return response()->json(['message' => 'Documento subido correctamente']);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json(['error' => 'Error al guardar el documento', 'detalle' => $e->getMessage()], 500);
    }
}

public function downloadDoc($thisDoc)
{
 $docInfo=Document::where("id",$thisDoc)->select("isSensitive","documentName","folderPath")->first();
 if (!$docInfo)
 {
    return response()->json("No se encontro este archivo.");
 }
 $path =ltrim($docInfo->folderPath . "/" . $docInfo->documentName);
//Hacer endpoint para descargar documentos PARA ABOGADOS 
/*if (Laywer::where("user_id",Auth::user()->id )->exists())
   {
        return Storage::disk("estudioLegal")->download($path);
   }
*/
 $user = $request->user(); 
  
 if ($docInfo->isSensitive == 0)
    {   
    Logger::create([
    "who" => $user->id,
    "doc"=>$thisDoc,
    "details" => $user->name." descargo el documento: " . $docInfo->documentName . " el dia " . $this->now,
]);
    
    return Storage::disk("estudioLegal")->download($path);
    //return Storage::disk("private")->download($path);        
    }

$petition = DownloadRequest::where("document_id",$thisDoc)->where("requested_by",1)->orderBy('created_at', 'desc')->first();
    if (!$petition)
    {
        Logger::create([
    "who" => $user->id,
    "doc"=>$thisDoc,
    "details" => $user->name ." intento descargar el documento: " . $docInfo->documentName . " el dia " . $this->now,
]);

        return response()->json([
           "statusP"=> "Para este documento se ocupa permisos de descarga, favor solicite un permiso"
        ]);
    }

    if (is_null($petition->status))
    {
        return response()->json([
           "statusP"=> "Su solicitud aun esta en proceso"
        ]);
    }

    if ( $petition->status==1)
    {
        Logger::create([
    "who" => $user->id,
    "doc"=>$thisDoc,
    "details" => $user->name ." descargo el documento: " . $docInfo->documentName . " el dia " . $this->now,
]); 
$petition->status = 0;
    $petition->save();

    return Storage::disk("estudioLegal")->download($path);
   
    //return Storage::disk("private")->download($path);
    }else
    {    Logger::create([
    "who" => $user->id,
    "doc"=>$thisDoc,
    "details" => $user->name ." intento descargar el documento: " . $docInfo->documentName . " el dia " . $this->now." pero su solicitud fue rechazada",
]);
  
        return response()->json([
           "statusP"=> "Lo sentimos, su peticion de descarga fue rechazada. Intente en un futuro"
        ]);
    }    
}

 public function downloadRequest(Document $thisDoc)
{ 
 $user = $request->user(); 
    if (downloadRequest::where("document_id",$thisDoc)->where("requested_by",$user->id)->whereNull("status")->exists())
    {
        return response()->json([
           "statusP"=>"Ya existe una solicitud pendiente para este documento"
        ]);
    }
   $requestNum= DownloadRequest::create([
        "document_id"=>$thisDoc->id,
        "requestDate"=>$this->now,
        "document_name"=>$thisDoc->documentName,
        "requested_by_name"=>$user->name,
        "requested_by"=>$user->id,  //Auth::user()->id,
    ]);
Logger::create([
    "who" => $user->id,
    "doc"=>$thisDoc,
    "details" => $user->name ." solicito la descarga del documento " . $thisDoc->documentName . " el dia " . $this->now." con el numero de solicitud " . $requestNum->id,
]);


    return response()->json([
    "status"=>"Solicitud procesada con exito",
"Numero de solicitud"=>$requestNum->id,
]);

}


}