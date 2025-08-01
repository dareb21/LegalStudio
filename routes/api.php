<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\AdminController;


Route::middleware(['auth:sanctum'])->group( function (){
    Route::get("/dashboard",[GeneralController::class, 'dashboard']);
    Route::get("/dirs",[GeneralController::class, 'showDirs']);
    Route::get("/thisDir",[GeneralController::class, 'showThisDir']);
    Route::post("/makeDir",[GeneralController::class, 'makeDir']);
    Route::post("/uploadDoc",[GeneralController::class, 'uploadDoc']);

    Route::middleware(['isAdmin:admin'])->group(function () {
        Route::get('/users', [AdminController::class, 'showUsers']);
        Route::post('/newUser', [AdminController::class, 'newUser']);
        Route::patch('/banThisUser/{userId}', [AdminController::class, 'banThisUser']);
        Route::patch('/unBanThisUser/{userId}', [AdminController::class, 'unBanThisUser']);   
        
    });
});
