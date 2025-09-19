<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAnalytics
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'analytics:' . $request->user()->id;
        
        // Ограничиваем до 60 запросов в минуту на пользователя
        if (RateLimiter::tooManyAttempts($key, 60)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'error' => 'Слишком много запросов к аналитике. Попробуйте через ' . $seconds . ' секунд.'
            ], 429);
        }
        
        RateLimiter::hit($key, 60); // TTL 60 секунд
        
        return $next($request);
    }
} 