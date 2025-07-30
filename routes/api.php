<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneralController;


Route::middleware(['auth:sanctum'])->group( function (){
Route::get("/dashboard",[GeneralController::class, 'dashboard']);

Route::get("/dirs",[GeneralController::class, 'showDirs']);
Route::post("/makeDir",[GeneralController::class, 'makeDir']);
Route::post("/uploadDoc",[GeneralController::class, 'uploadDoc']);


});
