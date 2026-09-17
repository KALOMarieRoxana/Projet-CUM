<?php

namespace App\Http\Controllers;

use App\Models\Citoyen;
use Illuminate\Http\Request;

class CitoyenController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * LISTE DES CITOYENS
     * ═══════════════════════════════════════════════════════════
     */
    public function index(Request $request)
    {
        $query = Citoyen::query();

        // Filtre statut
        if ($request->filled('statut')) {
            if ($request->statut === 'actif') {
                $query->where('actif', true);
            } elseif ($request->statut === 'desactive') {
                $query->where('actif', false);
            }
        }

        // Recherche
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nom', 'LIKE', "%{$q}%")
                    ->orWhere('prenom', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%")
                    ->orWhere('contact', 'LIKE', "%{$q}%");
            });
        }

        $citoyens = $query->latest()->paginate(20);

        $stats = [
            'total'     => Citoyen::count(),
            'actifs'    => Citoyen::where('actif', true)->count(),
            'desactives'=> Citoyen::where('actif', false)->count(),
        ];

        return view('super-admin.citoyens.index', compact('citoyens', 'stats'));
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * DÉTAILS D'UN CITOYEN
     * ═══════════════════════════════════════════════════════════
     */
    public function show($id)
    {
        $citoyen = Citoyen::with(['demandes.demandeActes.typeActe'])
            ->findOrFail($id);

        return view('super-admin.citoyens.show', compact('citoyen'));
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * DÉSACTIVER UN CITOYEN
     * ═══════════════════════════════════════════════════════════
     */
    public function desactiver(Request $request, $id)
    {
        $request->validate([
            'raison' => 'nullable|string|max:500',
        ]);

        $citoyen = Citoyen::findOrFail($id);

        $citoyen->update([
            'actif'                 => false,
            'desactive_le'          => now(),
            'raison_desactivation'  => $request->raison,
        ]);

        // ✅ Déconnecter toutes ses sessions
        \DB::table('sessions')->where('user_id', $citoyen->id_citoyens)->delete();

        return back()->with('success', "Le compte de {$citoyen->prenom} {$citoyen->nom} a été désactivé.");
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * RÉACTIVER UN CITOYEN
     * ═══════════════════════════════════════════════════════════
     */
    public function reactiver($id)
    {
        $citoyen = Citoyen::findOrFail($id);

        $citoyen->update([
            'actif'                 => true,
            'desactive_le'          => null,
            'raison_desactivation'  => null,
        ]);

        return back()->with('success', "Le compte de {$citoyen->prenom} {$citoyen->nom} a été réactivé.");
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * SUPPRIMER UN CITOYEN
     * ═══════════════════════════════════════════════════════════
     */
    public function destroy($id)
    {
        $citoyen = Citoyen::findOrFail($id);

        $nomComplet = "{$citoyen->prenom} {$citoyen->nom}";

        // ✅ Supprimer aussi ses demandes
        $citoyen->demandes()->delete();
        $citoyen->delete();

        return redirect()
            ->route('super-admin.citoyens.index')
            ->with('success', "Le citoyen {$nomComplet} a été supprimé définitivement.");
    }
}