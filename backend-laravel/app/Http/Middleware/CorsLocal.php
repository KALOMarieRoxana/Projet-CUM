<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsLocal
{
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');

        $allowedOrigins = [
            'http://127.0.0.1:5173',
            'http://localhost:5173',
        ];

        if (in_array($origin, $allowedOrigins, true)) {
            return $next($request)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        }

        return $next($request);
    }
}
