<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaiementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,super_admin']);
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * LISTE DES PAIEMENTS + RECHERCHE PAR RÉFÉRENCE
     * ═══════════════════════════════════════════════════════════
     */
    public function index(Request $request)
    {
        $demandes = null;
        $reference = $request->input('reference');

        // ✅ Recherche UNIQUEMENT si l'admin tape une référence
        if ($reference) {
            $demandes = Demande::with(['citoyen', 'demandeActes.typeActe'])
                ->where('reference', 'LIKE', "%{$reference}%")
                ->whereIn('statut', ['acceptée', 'acceptee'])
                ->latest()
                ->get();
        }

        // ✅ Statistiques globales
        $stats = [
            'total'    => Demande::whereIn('statut', ['acceptée', 'acceptee'])->count(),
            'paye'     => Demande::where('est_paye', true)->count(),
            'non_paye' => Demande::where('est_paye', false)
                                ->whereIn('statut', ['acceptée', 'acceptee'])
                                ->count(),
        ];

        return view('admin.paiements.index', compact('demandes', 'stats', 'reference'));
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * MARQUER COMME PAYÉ (espèces uniquement)
     * ═══════════════════════════════════════════════════════════
     */
    public function marquerPaye($id)
    {
        $demande = Demande::findOrFail($id);

        if ($demande->est_paye) {
            return back()->with('error', 'Cette demande est déjà payée.');
        }

        if (!in_array($demande->statut, ['acceptée', 'acceptee'])) {
            return back()->with('error', 'Seules les demandes acceptées peuvent être payées.');
        }

        $demande->update([
            'est_paye'      => true,
            'date_paiement' => now(),
            'encaisse_par'  => Auth::id(),
        ]);

        return back()->with('success', "Paiement encaissé en espèces pour la référence {$demande->reference}.");
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * ANNULER UN PAIEMENT
     * ═══════════════════════════════════════════════════════════
     */
    public function annuler($id)
    {
        $demande = Demande::findOrFail($id);

        $demande->update([
            'est_paye'      => false,
            'date_paiement' => null,
            'encaisse_par'  => null,
        ]);

        return back()->with('success', "Paiement annulé pour la référence {$demande->reference}.");
    }
}