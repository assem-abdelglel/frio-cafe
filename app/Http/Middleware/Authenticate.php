<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (Auth::guard($guards[0] ?? 'sanctum')->check()) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthenticated'], 401);
    }
}
