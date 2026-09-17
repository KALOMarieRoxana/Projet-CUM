<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'login',        // ⬅️ AJOUTE
        'logout',       // ⬅️ AJOUTE
        'register',     // ⬅️ AJOUTE
    ];
}