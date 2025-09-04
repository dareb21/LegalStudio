<?php

namespace App\Http\Controllers;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


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
$roleCookie = cookie(
    'user_role',
    $user->role,
    60*24*7,
    '/',
    null,
    true,      // secure
    false,     // httpOnly = false, para que JS pueda leerla
    false,
    'None'
);
return redirect()->to('http://localhost:5173')->withCookies([$roleCookie]);

        /*  $token = $user->createToken("auth_token")->plainTextToken;          
$cookie = cookie(
        'auth_token',         
        $token,              
        60*24*7,           
        '/',                 
        '.midominio.com/',    
        true,                
        true,                
        false,                
        'None'                
    );*/

    return redirect()->to('https://app.midominio.com/oauth/success')->withCookie($cookie);
        }
    catch(Exception $e)
    {
        return  response()->json(["Error"=>"Ha habido un error al autentificarse con google","Details"=> $e->getMessage() ]);

    }


}
}