<?php

namespace App\Http\Controllers;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
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
                
                return abort(401,"Correo no Autorizado");
            }
            $token = $user->createToken("auth_token")->plainTextToken;   
    return response()->json([
    "status"=>"ok", 
    "email"=>$user->email,
    "name"=>$user->name,
    "token"=>$token,
    "typeToken"=>"Bearer",
    "googlePhoto"=>$googleUser->getAvatar(),
    "role"=>$user->role
]);

        }
    catch(Exception $e)
    {
        return  response()->json(["Error"=>"Ha habido un error al autentificarse con google","Details"=> $e->getMessage() ]);

    }


}
public function hi()
{
return response()->json("Hi");
}
}