<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;

class VerifierRoleAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Accès réservé aux administrateurs.'], 403);
        }
        return $next($request);
    }
}
