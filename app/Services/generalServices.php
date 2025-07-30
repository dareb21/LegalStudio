<?php
namespace App\Services;
Use App\Models\Record;
use Illuminate\Support\Facades\Storage;
Class generalServices
{
    public function uploadDocs(Array $request)
    {
        
    }

public function makeDir()
{
        $dirName= "Prueba desde laravel"; 
        Storage::makeDirectory($dirName);

}

    public function docsForms()
    {
        
    }
}