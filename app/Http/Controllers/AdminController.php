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
    return response()->json(User::paginate(10));
    }

    public function banThisUser($userId)
    {
     
        $user = User::select("name")->where("id",$userId)->first();   
        User::where("id", $userId)->update(["banned" => 1]);
        //Log::info(Auth::user()->name ." bloqueo a ". $user->name ." a las " . $this->now);    
    return response()->json("Usuario bloqueado con exito");
    }

    public function unBanThisUser($userId)
    {
        $user = User::select("name")->where("id",$userId)->first();
        User::where("id", $userId)->update(["banned" => 0]);
        //Log::info(Auth::user()->name ." desbloqueo a ". $user->name ." a las " . $this->now);  
    return response()->json("Usuario desbloqueado con exito");
    }

    public function newUser(Request $request)
    {
             User::create([
                 "name"=> $request->name,
                "birthday"=>$request->birthday,
                "email"=>$request->email,
                "phone"=>$request->phone,
                "role"=>$request->role,
            ]);
    return response()->json("Usuario creado con exito!"); 
    } 

public function editUser(Request $request,$userId)
{

}

public function viewLogs()
{

}

}
