<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            "email" => "required|unique:users,email",
            "phone" => "required|size:8",
            "role" => "required|in:abogado,asistente", 
        ]);
             User::create([
                 "name"=> $request->name,
                "birthday"=>$request->birthday,
                "email"=>$request->email,
                "phone"=>$request->phone,
                "role"=>$request->role,
            ]);
        //Log::info(Auth::user()->name ." creo un nuevo usuario bajo el nombre de  ". $request->name ."y le asigno el rol de ". $request->role. ". " . $this->now);      
    return response()->json("Usuario creado con exito!"); 
    } 

public function editUser(Request $request,$userId)
{
        $request->validate([
            "name" => "required|string|min:5",
            "birthday" => "required|date|before:today",
            "email" => "required|unique:users,email",
            "phone" => "required|size:8",
            "role" => "required|in:abogado,asistente", 
        ]);
        User::where("id", $userId)->update([
          "name"=> $request->name,
                "birthday"=>$request->birthday,
                "email"=>$request->email,
                "phone"=>$request->phone,
                "role"=>$request->role,
        ]);
return response()->json("Usuario actualizado con exito!"); 
  
}

public function viewLogs()
{

}

}
