<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Logger;

class AdminController extends Controller
{
     private $now;

    public function __construct()
    {
        $this->now = now()->setTimezone('America/Tegucigalpa')->format('Y-m-d H:i:s');
    }

    public function showUsers()
    {
    return response()->json(User::where("banned",0)->paginate(10));
    }

    public function banThisUser($userId, Request $request)
    {

    $userInfo=  User::findOrFail($userId);
if ($userInfo->role === "Admin")
{
    return response()->json("Accion no permitida",403);
}
     $userInfo->banned = 1;
     $userInfo->save();
     $userInfo->tokens()->delete();
     
 $user = $request->user(); 
     Logger::create([
    "who" => $user->id,
    "details" => $user->name ." bloqueo a " .$userInfo->name . " el dia " . $this->now,
]);
    return response()->json("Usuario bloqueado con exito");
    }

    public function unBanThisUser($userId,Request $request)
    { 
        $userInfo=  User::findOrFail($userId);
        $userInfo->banned = 0;
        $userInfo->save();
        
 $user = $request->user(); 
     Logger::create([
    "who" => $user->id,
    "details" =>$user->name . "quito el bloqueo a " .$userInfo->name . " el dia " . $this->now,
]);

    return response()->json("Usuario desbloqueado con exito");
    }

public function seeBans()
{
    $bans = User::where("banned",1)->paginate(10);
    return response()->json([
        "bans"=>$bans,
    ]);
}


    public function newUser(Request $request)
    {
      
 $user = $request->user(); 
     $newUser= User::create([
                 "name"=> $request->name,
                "birthday"=>$request->birthday,
                "email"=>$request->email,
                "phone"=>$request->phone,
                "role"=>$request->role,
            ]);

               Logger::create([
    "who" => $user->id,
    "details" =>$user->name . " creo un nuevo usuario llamado" .$newUser->name. " del tipo " .$newUser->role . " el dia " . $this->now,
]);
     
        //Log::info(Auth::user()->name ." creo un nuevo usuario bajo el nombre de  ". $request->name ."y le asigno el rol de ". $request->role. ". " . $this->now);      
    return response()->json("Usuario creado con exito!"); 
    } 

public function edition(Request $request,$userId)
{
    try
    {
    $userInfo = User::findOrFail($userId);
    
    $validated = $request->validate([
   "name"     => "string|filled",
   "birthday" => "date|before:today",
   "email"    => "email",
   "phone"    => "size:8",
   "role"     => "in:Asistente,Abogado"
]);
 $user = $request->user(); 

if (array_key_exists('email', $validated)) {
$emailTaken = User::where("email", $validated['email'])->first();
  if ($emailTaken)
  {
    return response()->json("Este correo ya esta asignado a otro usuario");
  }
}
  $array=[
        "name" =>"Nombre documento",
        "birthday"=>"Sensibilidad de documento",
        "email"=>"Descripcion de documento",
        "phone"=>"Importancia de caso",
        "role"=>"Nombre de Juez"
    ];
$changes = array_values(array_intersect_key($array, $validated));
$string=" ";

foreach ($changes as $change)
{
    $string.=",".$change;
}
$name=$userInfo->name;
User::where('id', $userId)->update($validated);
Logger::create([
    "who" => $user->id,
    "details" =>$user->name . " modifico los campos" .$string. "del usuario ".$name ." el dia ". $this->now
]);
} catch (\Illuminate\Validation\ValidationException $e) {
    return response()->json(['errors' => $e->errors()], 422);
}   
return response()->json("Usuario actualizado con exito!"); 
  
}

public function allLogs(Request $request)
{
    $request->validate([
    "dateStart" => "nullable|date|before_or_equal:today",
    "dateEnd"   => "nullable|date|after_or_equal:dateStart",
]);

    $dateStart = $request->dateStart;
    $dateEnd   = $request->dateEnd;

    $users = User::select("id","name")->get();
$logs = Logger::select("details")
    ->when($dateStart && $dateEnd, function ($query) use ($dateStart, $dateEnd) {
        $query->whereBetween(DB::raw('DATE(created_at)'), [$dateStart, $dateEnd]);
    })
    ->orderBy("created_at", "DESC")
    ->paginate(30);
    
    $details= [];
        foreach ($logs as $log)
        {
            $details[] = $log->details;
        }

    return response()->json([
        "users"=>$users,
        "details"=>$details,
        "pagination"=>$logs
    ]);
}

public function specificLog($userId,Request $request)
{
    $request->validate([
    "dateStart" => "nullable|date|before_or_equal:today",
    "dateEnd"   => "nullable|date|after_or_equal:dateStart",
]);

    $dateStart = $request->dateStart;
    $dateEnd   = $request->dateEnd;

    $userInfo = Logger::select("details")
        ->where("who", $userId)
        ->when($dateStart && $dateEnd, function ($query) use ($dateStart, $dateEnd) {
        $query->whereBetween(DB::raw('DATE(created_at)'), [$dateStart, $dateEnd]);
        })
     ->orderBy("created_at","DESC")
        ->paginate(30);

$details= [];
        foreach ($userInfo as $log)
        {
            $details[] = $log->details;
        }


    return response()->json([
        "details" => $details,
        "pagination"=>$userInfo
    
    ]);
}


}
