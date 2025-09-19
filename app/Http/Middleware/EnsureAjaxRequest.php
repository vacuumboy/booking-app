<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAjaxRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем что это AJAX запрос или API запрос
        if (!$request->ajax() && !$request->expectsJson()) {
            abort(403, 'Этот роут доступен только для AJAX запросов');
        }

        return $next($request);
    }
} 