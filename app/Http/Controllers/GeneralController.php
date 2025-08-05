<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Sof;
use Illuminate\Http\Request;
use  App\Models\Folder;
use App\Models\Document;
use App\Models\DownloadRequest;

class GeneralController extends Controller
{
    public function dashboard()
    {
    #Espacio Disponible, Espacio Total, % de espacio ocupado    
    $totalSpace = disk_total_space(storage_path());
    $freeSpace = disk_free_space(storage_path()); 

    $freeGB = round($freeSpace / 1024 / 1024 / 1024, 2); 
    $totalGB = round($totalSpace / 1024 / 1024 / 1024, 2);
    
    $usedGB = $totalGB - $freeGB;
    $porcentualUsedGB = round(($usedGB / $totalGB) * 100, 2);
    
    #Cuantos Docs hay 
    $docs = Document::count("id"); //indexar documente Id, creo que ya esta indexado

//El ultimo que subio
//Cuantos docs se subieron en los ultimos 3 meses.
return [
        "freeSpace"=>$freeGB,
        "totalSpace"=>$totalGB,
        "usedGb"=>$porcentualUsedGB,
        "howManyDocs"=>$docs
        ];
    }

    public function showDirs()
    {
    $dirs = Folder::select("id","folderName")->where("parentFolder",null)->where("jurisprudence",0)->paginate(10); //Indexar parentFolder   
    return  response()->json($dirs);  
}

public function showThisDir($thisDir)
{
    $thisDir = Folder::select("id","folderName")->where("parentFolder",$thisDir)->paginate(10);  //Indexar parentFolder 
    return  response()->json($thisDir);
}


    public function makeDir(Request $request)
    {
       $request->validate([
        "parentFolder"=>"integer|min:1",
        "folderName"=>"required|string|filled"
       ]); 

    $isRoot = false;
    $folderName = $request->input('folderName');
    $parentFolder = $request->input('parentFolder');

     if ($parentFolder == 0)
     {
        $parentFolder = null;
        $isRoot = true;
     }
     
    $newFolder= Folder::create([
        "folderName"=>$folderName ,
        "parentFolder" =>$parentFolder,
     ]);

     if ($isRoot)
     {
         Storage::disk('public')->makeDirectory($newFolder->id);
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
Storage::disk('public')->makeDirectory($fullPath);
}
     return response()->json("Carpeta Creada con exito");
    }

public function uploadDoc(Request $request,$thisDir)
    { 
  DB::beginTransaction();
try { 
    $dir=Folder::findOrFail($thisDir);
    //$folderPath = Folder::select("folderPath","id")->where("id",$thisDir)->first();
    // $file = $request->file('file');
    //$fileName = $file->getClientOriginalName();
    //$file->storeAs($dir->folderPath,$fileName,"public");

      Document::create([ 
          "documentName"   => $request->documentName,  //Esto es temporal, se sacara del $fileName
          "folder_id"      =>$dir->id,
          "description"    => $request->description,
          "judge"          => $request->judge,
          "whoMadeIt"      => Auth::user()->name,
          "dateOfUpload"   => now(),
          "isSensitive" =>0,
          "record"      => $request->record,
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
    $docInfo=Document::where("id",$thisDoc)->select("isSensitive")->first();
    if ($docInfo->isSensitive == 0)
    {
            return response()->json("descarga Normal");
    }

    $x = DownloadRequest::where("document_id",$thisDoc)->where("requested_by",Auth::user()->id)->orderBy('created_at', 'desc')->first();

    if (!$x)
    {
     return response()->json("Para este documento se ocupa permisos de descarga, favor solicite un permiso");  

    }

    if (is_null($x->status))
    {
        return response()->json("Su solicitud aun esta en proceso.");
    }

    if ( $x->status==1)
    {
        return response()->json("Su peticion de descarga fue Aprobada. Descargando...");
    }else
    {
        return response()->json("Lo sentimos, su peticion de descarga fue rechazada. Intente en un futuro");
    }    
}

 public function downloadRequest($thisDoc)
{
   $requestNum= DownloadRequest::create([
        "document_id"=>$thisDoc,
        "requestDate"=>now(),
        "requested_by"=>Auth::user()->id,
    ]);
return response()->json([
    "status"=>"Solicitud procesada con exito",
"Numero de solicitud"=>$requestNum->id,
]);

}


}