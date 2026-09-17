<?php

namespace App\Http\Controllers;

use App\Models\TypeActe;
use App\Models\TypeActeSupplement;
use Illuminate\Http\Request;

class TypeActeAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * LISTE DES TYPES D'ACTES
     * ═══════════════════════════════════════════════════════════
     */
    public function index()
    {
        $typesActes = TypeActe::withCount('supplements')->latest()->get();

        $stats = [
            'total'     => TypeActe::count(),
            'actifs'    => TypeActe::where('actif', true)->count(),
            'supplements' => TypeActeSupplement::count(),
        ];

        return view('super-admin.types-actes.index', compact('typesActes', 'stats'));
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * FORMULAIRE DE CRÉATION (MODAL ou vue)
     * ═══════════════════════════════════════════════════════════
     */
    public function create()
    {
        return view('super-admin.types-actes.create');
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * ENREGISTRER UN NOUVEAU TYPE
     * ═══════════════════════════════════════════════════════════
     */
    public function store(Request $request)
    {
        $request->validate([
            'type_acte'         => 'required|string|max:50|unique:type_actes,type_acte',
            'nom'               => 'required|string|max:255',
            'sigle'             => 'nullable|string|max:10',
            'montantStandardMG' => 'required|numeric|min:0',
            'montantExpressMG'  => 'required|numeric|min:0',
            'montantStandardFR' => 'required|numeric|min:0',
            'montantExpressFR'  => 'required|numeric|min:0',
        ]);

        TypeActe::create([
            'type_acte'         => strtolower($request->type_acte),
            'nom'               => $request->nom,
            'sigle'             => strtoupper($request->sigle ?? ''),
            'montantStandardMG' => $request->montantStandardMG,
            'montantExpressMG'  => $request->montantExpressMG,
            'montantStandardFR' => $request->montantStandardFR,
            'montantExpressFR'  => $request->montantExpressFR,
            'actif'             => true,
        ]);

        return redirect()
            ->route('super-admin.types-actes.index')
            ->with('success', 'Type d\'acte créé avec succès.');
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * FORMULAIRE D'ÉDITION
     * ═══════════════════════════════════════════════════════════
     */
    public function edit($id)
    {
        $type = TypeActe::with('supplements')->findOrFail($id);

        return view('super-admin.types-actes.edit', compact('type'));
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * METTRE À JOUR UN TYPE
     * ═══════════════════════════════════════════════════════════
     */
    public function update(Request $request, $id)
    {
        $type = TypeActe::findOrFail($id);

        $request->validate([
            'nom'               => 'required|string|max:255',
            'sigle'             => 'nullable|string|max:10',
            'montantStandardMG' => 'required|numeric|min:0',
            'montantExpressMG'  => 'required|numeric|min:0',
            'montantStandardFR' => 'required|numeric|min:0',
            'montantExpressFR'  => 'required|numeric|min:0',
        ]);

        $type->update([
            'nom'               => $request->nom,
            'sigle'             => strtoupper($request->sigle ?? ''),
            'montantStandardMG' => $request->montantStandardMG,
            'montantExpressMG'  => $request->montantExpressMG,
            'montantStandardFR' => $request->montantStandardFR,
            'montantExpressFR'  => $request->montantExpressFR,
        ]);

        return redirect()
            ->route('super-admin.types-actes.index')
            ->with('success', 'Type d\'acte mis à jour avec succès.');
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * SUPPRIMER UN TYPE
     * ═══════════════════════════════════════════════════════════
     */
    public function destroy($id)
    {
        $type = TypeActe::findOrFail($id);

        // Vérifier s'il y a des demandes liées
        if ($type->demandeActes()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer : des demandes utilisent ce type d\'acte.');
        }

        $type->supplements()->delete();
        $type->delete();

        return redirect()
            ->route('super-admin.types-actes.index')
            ->with('success', 'Type d\'acte supprimé avec succès.');
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * BASCULER ACTIF / INACTIF
     * ═══════════════════════════════════════════════════════════
     */
    public function toggleActif($id)
    {
        $type = TypeActe::findOrFail($id);
        $type->update(['actif' => !$type->actif]);

        $statut = $type->actif ? 'activé' : 'désactivé';

        return back()->with('success', "Type d'acte {$statut} avec succès.");
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * SUPPLEMENTS — CRUD
     * ═══════════════════════════════════════════════════════════
     */
    public function storeSupplement(Request $request, $typeId)
    {
        $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'prix_standard_mg' => 'required|numeric|min:0',
            'prix_express_mg'  => 'required|numeric|min:0',
            'prix_standard_fr' => 'required|numeric|min:0',
            'prix_express_fr'  => 'required|numeric|min:0',
        ]);

        TypeActeSupplement::create([
            'type_acte_id' => $typeId,
            'nom'          => $request->nom,
            'description'  => $request->description,
            'prix_standard_mg' => $request->prix_standard_mg,
            'prix_express_mg'  => $request->prix_express_mg,
            'prix_standard_fr' => $request->prix_standard_fr,
            'prix_express_fr'  => $request->prix_express_fr,
        ]);

        return back()->with('success', 'Supplément ajouté avec succès.');
    }

    public function destroySupplement($id)
    {
        $supplement = TypeActeSupplement::findOrFail($id);
        $supplement->delete();

        return back()->with('success', 'Supplément supprimé.');
    }
}