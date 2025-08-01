<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

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

}
