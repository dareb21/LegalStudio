<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use  App\Models\Folder;
use App\Models\Document;

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
    $dirs = Folder::select("id","folderName")->where("parentFolder",null)->paginate(10); //Indexar parentFolder   
    return  response()->json($dirs);  
}

public function showThisDir(Request $request)
{
    $thisDir = Folder::select("id","folderName")->where("parentFolder",$request->parentFolder)->paginate(10);  //Indexar parentFolder 
    return  response()->json($thisDir);
}


    public function makeDir(Request $request)
    {
       $request->validate([
        "parentFolder"=>"required|integer|min:0",
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



    public function uploadDoc(Request $request)
    { 
$request->validate([
        'folderId' => 'required|int|min:0', 
    ]);

  DB::beginTransaction();
try { 
    $folderPath = Folder::select("folderPath")->where("id",$request->folderId)->first();
//    $file = $request->file('file');
  //  $fileName = $file->getClientOriginalName();
    //$file->storeAs($folderPath,$fileName,"public");

      Document::create([ 
          "documentName"   => $request->documentName,  //Esto es temporal, se sacara del $fileName
          "folder_id"      =>$request->folderId,
          "description"    => $request->description,
          "judge"          => $request->judge,
          "whoMadeIt"      => Auth::user()->name,
          "dateOfUpload"   => now(),
          "record"      => $request->record,
        ]);
        DB::commit();

      return response()->json(['message' => 'Documento subido correctamente']);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json(['error' => 'Error al guardar el documento', 'detalle' => $e->getMessage()], 500);
    }
}



}