<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Регистрируем пользовательские middleware
        $middleware->alias([
            'salon' => \App\Http\Middleware\EnsureSalonUser::class,
            'master' => \App\Http\Middleware\EnsureMasterUser::class,
            'ajax' => \App\Http\Middleware\EnsureAjaxRequest::class,
            'throttle.analytics' => \App\Http\Middleware\ThrottleAnalytics::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
