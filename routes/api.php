<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LaywerController;

Route::middleware(['auth:sanctum'])->group( function (){
    Route::get("/dashboard",[GeneralController::class, 'dashboard']);
    Route::get("/dirs",[GeneralController::class, 'showDirs']);
    Route::get("/thisDir/{thisDir}",[GeneralController::class, 'showThisDir']);
    Route::post("/makeDir",[GeneralController::class, 'makeDir']);
    Route::post("/uploadDoc/{thisDir}",[GeneralController::class, 'uploadDoc']);
    Route::get("/downloadDoc/{thisDoc}",[GeneralController::class, 'downloadDoc']);
    Route::post("/downloadRequest/{thisDoc}",[GeneralController::class, 'downloadRequest']);

    Route::middleware(['isAdmin:admin'])->group(function () {
        Route::get('/users', [AdminController::class, 'showUsers']);
        Route::post('/newUser', [AdminController::class, 'newUser']);
        Route::patch('/banThisUser/{userId}', [AdminController::class, 'banThisUser']);
        Route::patch('/unBanThisUser/{userId}', [AdminController::class, 'unBanThisUser']);
        //ver logs
        //Editar
    });

    Route::middleware(['isLaywer:Abogado'])->group(function () {
        Route::get('/seeRequest', [LaywerController::class, 'seeRequest']);   
        Route::patch('/replyRequest/{thisRequest}', [LaywerController::class, 'replyRequest']);
        Route::delete('/deleteDoc/{thisDoc}', [LaywerController::class, 'deleteDoc']);
        Route::delete('/deleteDir/{thisDir}', [LaywerController::class, 'deleteDir']);
        Route::get('/recycle', [LaywerController::class, 'recycleCan']);
        Route::patch('/restore/{thisDoc}', [AdminController::class, 'restoreDoc']);
        Route::patch('/restoreDir/{thisDir}', [AdminController::class, 'restoreDir']);
        Route::patch('/finished/{thisDir}', [AdminController::class, 'finishThisCase']);
    });

});
