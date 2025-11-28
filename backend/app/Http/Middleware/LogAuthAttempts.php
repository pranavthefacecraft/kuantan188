<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogAuthAttempts
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('Auth middleware check', [
            'url' => $request->url(),
            'method' => $request->method(),
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'session_started' => session()->isStarted(),
            'session_data_keys' => array_keys(session()->all()),
            'guard' => config('auth.defaults.guard'),
        ]);

        return $next($request);
    }
}