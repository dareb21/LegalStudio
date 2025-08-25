<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LaywerController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
    Route::get("/dashboard",[GeneralController::class, 'dashboard']);

    Route::get("/dirs/{type}",[GeneralController::class, 'showDirs']);
    Route::get("/thisDir/{thisDir}",[GeneralController::class, 'showThisDir']);
    Route::get("/docsInThisDir/{thisDir}",[GeneralController::class, 'showDocs']);
    Route::post("/makeDir",[GeneralController::class, 'makeDir']);
    Route::post("/uploadDoc/{thisDir}",[GeneralController::class, 'uploadDoc']);
    Route::get("/downloadDoc/{thisDoc}",[GeneralController::class, 'downloadDoc']);
    Route::post("/downloadRequest/{thisDoc}",[GeneralController::class, 'downloadRequest']);

        Route::get('/users', [AdminController::class, 'showUsers']);
        Route::post('/newUser', [AdminController::class, 'newUser']);
        Route::patch('/banThisUser/{userId}', [AdminController::class, 'banThisUser']);
        Route::patch('/unBanThisUser/{userId}', [AdminController::class, 'unBanThisUser']);
        Route::put('/editUser', [AdminController::class, 'editUser']);
        //ver todos los logs
        //Editar
       
        Route::get('/seeRequest', [LaywerController::class, 'seeRequest']);   
        Route::patch('/replyRequest/{thisRequest}', [LaywerController::class, 'replyRequest']);
        Route::delete('/deleteDoc/{thisDoc}', [LaywerController::class, 'deleteDoc']);
        Route::delete('/deleteDir/{thisDir}', [LaywerController::class, 'deleteDir']);
        Route::get('/recycle/{dirType}', [LaywerController::class, 'recycleCan']);
        Route::patch('/restore/{thisDoc}', [LaywerController::class, 'restoreDoc']);
        Route::patch('/restoreDir/{thisDir}', [LaywerController::class, 'restoreDir']);
        Route::patch('/finished/{thisDir}', [LaywerController::class, 'finishThisCase']);
        Route::get('/logs', [LaywerController::class, 'logs']);    
        Route::get("/home",[GeneralController::class, 'home']);
    