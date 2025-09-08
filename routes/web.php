<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

Route::get("/login", [LoginController::class, "logIn"])->name('login');
Route::get("/auth/google/callback",[LoginController::class,"handdleCallBack"]);
