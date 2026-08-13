<?php

namespace App\Http\Controllers;

use App\Models\TypeActe;

class TypeActeController extends Controller
{
    public function index()
    {
        $types = TypeActe::select('id_types-actes', 'nom', 'description', 'type_acte')->get();
        return response()->json(['types' => $types]);
    }
}
