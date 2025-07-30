<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
    $docs = Document::count("id");

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
    $dirs= Storage::disk('public')->directories();
    return  response()->json($dirs);  
}




    public function makeDir(Request $request)
    {
    $route = $request->input('route');
    $path =Storage::disk('public')->makeDirectory($route);

    }

    public function uploadDoc(Request $request)
    {
$request->validate([
        'folder' => 'required|string', 
    ]);

  DB::beginTransaction();
try { 
    $file = $request->file('file');
    $fileName = $file->getClientOriginalName();
    $filePath = $file->store($request->folder,$fileName ,'public');
      Document::create([ 
          "documentPath"   => $filePath,
          "documentName"    =>$fileName,
            "description"    => $request->description,
            "judge"          => $request->judge,
            "whoMadeIt"      => Auth::user()->Name,
            "dateOfUpload"   => now(),
            "record_id"      => 1,
        ]);
        DB::commit();

        return response()->json(['message' => 'Documento subido correctamente']);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json(['error' => 'Error al guardar el documento', 'detalle' => $e->getMessage()], 500);
    }
}



}