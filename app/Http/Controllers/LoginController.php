<?php

namespace App\Http\Controllers;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

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
                return abort(404);
            }

$params = "?success=true"; 
        return redirect()->to('https://estudiolegalhn.com/'.$params.'/'.$user->id);

$authToken = $user->createToken("auth_token")->plainTextToken;  
$userInfo=[];
$userInfo = [
    "role" => $user->role,
    "name" => $googleUser->getName(),
    "photo" => $googleUser->getAvatar(),
    "email" => $googleUser->getEmail()
];
$encrypToken = Crypt::encryptString($authToken);

        $cookieRole = cookie(
            "user_role",
             json_encode($userInfo),
            20,
            '/',
             '.estudiolegalhn.com',
            true,      // secure
            false,     // httpOnly (false para que JS pueda leerla)
            false,
            'None'
        );
        
        $authCookie = cookie(
            "tsepf",
            $encrypToken,
            60,
            "/",
            ".estudiolegalhn.com",
            true,
            true,
            false,
            "none" 
        );
        
   }
    catch(Exception $e)
    {
        return  response()->json(["Error"=>"Ha habido un error al autentificarse con google","Details"=> $e->getMessage() ]);

    }
}

public function roleUser(Request $request)
{
        $role = $request->cookie("user_role");
        return response()->json([
            "userRole"=>$role,
        ]);
}
}