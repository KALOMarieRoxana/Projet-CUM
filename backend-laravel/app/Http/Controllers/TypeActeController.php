<?php

namespace App\Http\Controllers;

use App\Models\TypeActe;

class TypeActeController extends Controller
{
    public function index()
    {
        $types = TypeActe::all();
        return response()->json([
            'types' => $types
        ], 200);
    }
}
