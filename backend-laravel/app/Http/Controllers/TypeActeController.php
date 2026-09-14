<?php

namespace App\Http\Controllers;

use App\Models\TypeActe;

class TypeActeController extends Controller
{
   public function index()
    {
        $types = TypeActe::with(['supplements' => function ($q) {
            $q->where('actif', true)->orderBy('ordre');
        }])->get();

        return response()->json([
            'types' => $types
        ]);
    }
}
