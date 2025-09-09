<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LaywerController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get("/authUser",[LoginController::class, 'authUser']);
    
    Route::middleware("notExchange")->group(function (){
        Route::get("/refreshUser",[LoginController::class, 'refreshUser']);
        Route::post("/uploadDoc/{thisDir}",[GeneralController::class, 'uploadDoc']); 
        Route::get("/dashboard",[GeneralController::class, 'dashboard']);
        Route::get("/dirs/{type}",[GeneralController::class, 'showDirs']);
        Route::get("/thisDir/{thisDir}",[GeneralController::class, 'showThisDir']);
        Route::get("/docsInThisDir/{thisDir}",[GeneralController::class, 'showDocs']);
        Route::post("/makeDir",[GeneralController::class, 'makeDir']);
        Route::get("/downloadDoc/{thisDoc}",[GeneralController::class, 'downloadDoc']);
        Route::post("/downloadRequest/{thisDoc}",[GeneralController::class, 'downloadRequest']);
    
        Route::middleware("isAdmin")->group(function(){
            Route::get('/users', [AdminController::class, 'showUsers']);
            Route::post('/newUser', [AdminController::class, 'newUser']);
            Route::patch('/banThisUser/{userId}', [AdminController::class, 'banThisUser']);
            Route::patch('/unBanThisUser/{userId}', [AdminController::class, 'unBanThisUser']);
            Route::put('/editUser/{userId}', [AdminController::class, 'editUser']);
            Route::get('/seeBans', [AdminController::class, 'seeBans']);
            Route::get("/allLogs", [AdminController::class, 'allLogs']);
        });

        Route::middleware("isLawyer")->group(function(){
            Route::get('/seeRequest', [LaywerController::class, 'seeRequest']);   
            Route::patch('/replyRequest/{thisRequest}', [LaywerController::class, 'replyRequest']);
            Route::delete('/deleteDoc/{thisDoc}', [LaywerController::class, 'deleteDoc']);
            Route::delete('/deleteDir/{thisDir}', [LaywerController::class, 'deleteDir']);
            Route::get('/recycle/{dirType}', [LaywerController::class, 'recycleCan']);
            Route::patch('/restore/{thisDoc}', [LaywerController::class, 'restoreDoc']);
            Route::patch('/restoreDir/{thisDir}', [LaywerController::class, 'restoreDir']);
            Route::patch('/finished/{thisDir}', [LaywerController::class, 'finishThisCase']);
            Route::get('/logs', [LaywerController::class, 'quickLogs']);    
            Route::put('/updateDir/{thisDir}', [LaywerController::class, 'updateDir']);  
            Route::put('/updateDoc/{thisDoc}', [LaywerController::class, 'updateDoc']);
            Route::get('/docActivity/{thisDoc}', [LaywerController::class, 'docActivity']);  
        }); 
    });
   

});
