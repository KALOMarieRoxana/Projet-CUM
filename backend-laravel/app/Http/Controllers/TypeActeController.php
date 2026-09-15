<?php

namespace App\Http\Controllers;

use App\Models\TypeActe;
use Illuminate\Http\Request;

class TypeActeController extends Controller
{
    /**
     * Liste des types d'actes (API)
     */
    public function index()
    {
        $types = TypeActe::with('supplements')->get();
        return response()->json(['types' => $types]);
    }

    /**
     * Modifier un type d'acte (Admin)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'montantStandardMG' => 'required|numeric|min:0',
            'montantExpressMG' => 'required|numeric|min:0',
            'montantStandardFR' => 'required|numeric|min:0',
            'montantExpressFR' => 'required|numeric|min:0',
        ]);

        $type = TypeActe::findOrFail($id);

        $type->update([
            'montantStandardMG' => $request->montantStandardMG,
            'montantExpressMG' => $request->montantExpressMG,
            'montantStandardFR' => $request->montantStandardFR,
            'montantExpressFR' => $request->montantExpressFR,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prix mis à jour avec succès.',
            'type' => $type,
        ]);
    }
}