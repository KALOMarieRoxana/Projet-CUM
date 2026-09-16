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
    // Liste DEMANDE PAYE

    public function liste(Request $request)
    {
        // ✅ Mois sélectionné (par défaut : mois actuel)
        $moisSelectionne = $request->input('mois', now()->format('Y-m'));

        [$annee, $mois] = explode('-', $moisSelectionne);

        // ✅ Liste des demandes payées ce mois
        $demandes = Demande::with(['citoyen', 'demandeActes.typeActe', 'traiteur'])
            ->where('est_paye', true)
            ->whereYear('date_paiement', $annee)
            ->whereMonth('date_paiement', $mois)
            ->orderBy('date_paiement', 'desc')
            ->get();

        $totalMois = $demandes->sum('prix_total');
        $nombreMois = $demandes->count();

        // ✅ Liste des 12 derniers mois
        $listeMois = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $anneeM = $date->year;
            $moisM = $date->month;

            $count = Demande::where('est_paye', true)
                ->whereYear('date_paiement', $anneeM)
                ->whereMonth('date_paiement', $moisM)
                ->count();

            $total = Demande::where('est_paye', true)
                ->whereYear('date_paiement', $anneeM)
                ->whereMonth('date_paiement', $moisM)
                ->sum('prix_total');

            $listeMois[] = [
                'valeur' => sprintf('%04d-%02d', $anneeM, $moisM),
                'label'  => $date->translatedFormat('F Y'),
                'count'  => $count,
                'total'  => $total,
            ];
        }

        $data = compact('demandes', 'totalMois', 'nombreMois', 'listeMois', 'moisSelectionne');

        // ✅ Choisir la vue selon la route appelée
        if ($request->routeIs('super-admin.*')) {
            return view('super-admin.paiements.liste', $data);
        }

        return view('admin.paiements.liste', $data);
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