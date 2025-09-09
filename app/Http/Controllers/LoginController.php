<?php

namespace App\Http\Controllers;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
  
 private $now;

    public function __construct()
    {
        $this->now = now()->setTimezone('America/Tegucigalpa')->format('Y-m-d H:i:s');
    }
  
 public function logIn()
    {
        
        return Socialite::driver("google")->stateless()->redirect();
    }

public function handdleCallBack()
{
try
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where("email",$googleUser->getEmail())->first(); 
        
        if (!$user || $user->banned == 1)
            {   
                return abort(404);
            }
$tempToken = $user->createToken("temp_token", ['exchange'], now()->addMinutes(5))->plainTextToken;
$paramTempToken = "tempToken=".$tempToken; 
$paramUrlPhoto = "photo=".$googleUser->getAvatar();
return redirect()->to('https://estudiolegalhn.com/#'.$paramTempToken.'&'.$paramUrlPhoto);

   }
    catch(Exception $e)
    {
        return  response()->json(["Error"=>"Ha habido un error al autentificarse con google","Details"=> $e->getMessage() ]);

    }
}

public function authUser(Request $request)
{
         $authToken= $request->user()->currentAccessToken();
        if (!$authToken)
        {
            return response()->json(["error" => "No se recibio el token de acceso."], 401);
        }

    if (!$authToken->can('exchange')) {
        $authToken->delete();
        return response()->json(["error" => "Token sin permisos"], 403);
    }

     if ($authToken->expires_at && $authToken->expires_at->isPast()) {
        $authToken->delete();
        return response()->json(["error" => "Token expirado"], 401);
    }
 $user= $authToken->tokenable;
$authToken->delete();
 $abilities = match($user->role) {
    "Asistente" => ['Asistente'],
    "Abogado"   => ['Abogado'],
    "Admin"     => ['Admin'],
  default => response()->json(["error" => "Rol no autorizado"], 401),

};

 $token = $user->createToken("auth_token", $abilities, now()->addMinutes(10))->plainTextToken;
  return response()->json([
        "name"=>$user->name,
        "email"=>$user->email,
        "role"=>$user->role,
    ])->header("Authorization","Bearer ".$token);
}

public function refreshUser(Request $request)
{
  $oldToken= $request->user()->currentAccessToken();
  if (!$oldToken)
  {
    return response()->json([
        "Message"=>"Token no valido",
    ]);
  }
  $user= $oldToken->tokenable;
  $abilities = $oldToken->abilities; 
  $oldToken->delete();
 $token = $user->createToken("auth_token", $abilities, now()->addMinutes(30))->plainTextToken;
 
 return response()->json([
    "status"=>"ok"
 ])->header("Authorization","Bearer ".$token);  
}

}