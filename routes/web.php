<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/auth/redirect',[LoginController::class,"login"])->name("login"); 
Route::get("/auth/google/callback",[LoginController::class,"handdleCallBack"]);


