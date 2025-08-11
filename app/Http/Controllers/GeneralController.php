<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use  App\Models\Folder;
use App\Models\Document;
use App\Models\DownloadRequest;
use App\Jobs\DeleteJob;
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

public function home()
{
    return view("prueba");
}
    public function dashboard()
    { 
     DeleteJob::dispatch();   
     
    #Espacio Disponible, Espacio Total, % de espacio ocupado    
    $totalSpace = disk_total_space("C:\LegalStudio");
    $freeSpace = disk_free_space("C:\LegalStudio"); 

    $freeGB = round($freeSpace / 1024 / 1024 / 1024, 2); 
    $totalGB = round($totalSpace / 1024 / 1024 / 1024, 2);
    
    $usedGB = $totalGB - $freeGB;
    $porcentualUsedGB = round(($usedGB / $totalGB) * 100, 2);
    
    #Cuantos Docs hay 
    $docs = Document::count("id"); //indexar documente Id, creo que ya esta indexado


    //Logs ultimos 10
    //Peticiones de descarga pendientes
return [
        "freeSpace"=>$freeGB,
        "totalSpace"=>$totalGB,
        "usedGb"=>$porcentualUsedGB,
        "howManyDocs"=>$docs,
        ];
    }
//Cambiar nombre de carpeta
    public function showDirs(Request $request)
    {
    $request->validate([
        "type"=>"required|string|in:active,finished,jurisprudence"
    ]); 
    $dirs = Folder::select("id","folderName")->where("parentFolder",null)->where("type",$request->type)->paginate(10); //Indexar parentFolder   
    return  response()->json($dirs);  
}

public function showThisDir($thisDir)
{
    $dirs = Folder::select("id","folderName","important")->where("parentFolder",$thisDir)->get();  //Indexar este campo
    $docs =Document::where("folder_id",$thisDir)->OrderBy("created_at","desc")->paginate(10);          //Indexar esto tambien 
    return  response()->json([
        "dirs"=>$dirs,
        "docs"=>$docs
    ]);
}


    public function makeDir(Request $request)
    {
       $request->validate([
        "parentFolder"=>"integer|min:1",
        "important"=>"integer|in:1,2,3",
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
        "important"=> "3",
     ]);
     if ($isRoot)
     {
         Storage::disk('legalStudio')->makeDirectory($newFolder->id);
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
Storage::disk('legalStudio')->makeDirectory($fullPath);
}
//Log::info(Auth::user()->name ." Creo la carpeta: ".  $folderName ." a las " . $this->now);   

     return response()->json("Carpeta Creada con exito");
    }

   

public function uploadDoc(Request $request,$thisDir)
    { 
      $request->validate([
         "important"=>"required|integer|in:1,2,3",
        
      ]);  
  DB::beginTransaction();
try { 
    $folder = Folder::select("folderPath","id")->where("id",$thisDir)->first();
    $file = $request->file('file');
    $fileName = $file->getClientOriginalName();
    
if($folder)
{
  if (is_null($folder->folderPath))
    {
        $file->storeAs("/".$folder->id,$fileName,"legalStudio");
    }else
    {
        $file->storeAs($folder->folderPath,$fileName,"legalStudio");
    }
}

    
      Document::create([ 
          "documentName"   => $fileName,  
          "folder_id"      =>$folder->id,
          "folderPath" =>   $folder->folderPath,
          "description"    => $request->description,
          "judge"          => $request->judge,
          "whoMadeIt"      => "Carlos",//Auth::user()->name,
          "dateOfUpload"   => $this->now,
          "isSensitive"    =>1,
          "record"      => $request->record,
          "important" => $request->important
        ]);
        DB::commit();
  //  Log::info(Auth::user()->name ." subio el archivo: ".  $request->documentName ." a las " . $this->now);   
        
      return response()->json(['message' => 'Documento subido correctamente']);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json(['error' => 'Error al guardar el documento', 'detalle' => $e->getMessage()], 500);
    }
}

public function downloadDoc($thisDoc)
{
    $docInfo=Document::where("id",$thisDoc)->select("isSensitive","documentName","folderPath")->first();
     $path =ltrim($docInfo->folderPath . "/" . $docInfo->documentName);
    if ($docInfo->isSensitive == 0)
    {   
    //    Log::info(Auth::user()->name ." descargo el archivo: ".  $docInfo->documentName ." a las " . $this->now);      
         return Storage::disk("legalStudio")->download($path);    
    }

    $petition = DownloadRequest::where("document_id",$thisDoc)->where("requested_by",Auth::user()->id)->orderBy('created_at', 'desc')->first();

    if (!$petition)
    {
    //Log::info(Auth::user()->name ."intento  descargar el archivo: ".  $docInfo->documentName ." a las " . $this->now);      
     return response()->json("Para este documento se ocupa permisos de descarga, favor solicite un permiso");  

    }

    if (is_null($petition->status))
    {
        return response()->json("Su solicitud aun esta en proceso.");
    }

    if ( $petition->status==1)
    {
      //  Log::info(Auth::user()->name ." obtuvo permiso y descargo el archivo: ".  $docInfo->documentName ." a las " . $this->now);  
    return Storage::disk("legalStudio")->download($path);         
    }else
    {
        return response()->json("Lo sentimos, su peticion de descarga fue rechazada. Intente en un futuro");
    }    
}

 public function downloadRequest($thisDoc)
{
   $file = Document::select("documentName")->where("id",$thisDoc)->first(); 
   $requestNum= DownloadRequest::create([
        "document_id"=>$thisDoc,
        "requestDate"=>$this->now,
        "requested_by"=>Auth::user()->id,
    ]);
//Log::info(Auth::user()->name ." Solicito una peticion para descargar el archivo: ".  $file->documentName ." a las " . $this->now);   
return response()->json([
    "status"=>"Solicitud procesada con exito",
"Numero de solicitud"=>$requestNum->id,
]);

}


}