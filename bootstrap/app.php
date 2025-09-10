<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->alias([
            'isAdmin' => \App\Http\Middleware\AdminMiddleware::class,
            'isLawyer'=>\App\Http\Middleware\laywerMiddleware::class,
            'notExchange'=>\App\Http\Middleware\notExchangeMiddleware::class,
            'logsAndDown'=>\App\Http\Middleware\logsAndDown::class,
        ]);
        $middleware->validateCsrfTokens(except:[
        ]);
})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
