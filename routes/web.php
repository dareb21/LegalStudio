<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\GeneralController
;
Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/auth/redirect',[LoginController::class,"login"])->name("login"); 
Route::get("/auth/google/callback",[LoginController::class,"handdleCallBack"]);

 Route::post("/uploadDoc/{thisDir}",[GeneralController::class, 'uploadDoc']);
Route::get("/downloadDoc/{thisDoc}",[GeneralController::class, 'downloadDoc']);
  
 route::get("/home",[GeneralController::class, 'home']);
