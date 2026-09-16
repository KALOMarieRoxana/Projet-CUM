<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard pour l'Admin standard
     */
    public function adminIndex(Request $request)
    {
        $query = Demande::with([
            'citoyen',
            'demandeActes.typeActe',
            'demandeActes.acte'
        ]);

        if ($request->has('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        $demandes = $query->latest()->get();

        // ✅ Statistiques (compatibles avec toutes les variantes)
        $totalDemandes     = Demande::count();
        $demandesEnAttente = Demande::whereIn('statut', ['en_attente', 'en attente'])->count();
        $demandesAcceptees = Demande::whereIn('statut', ['acceptée', 'acceptee'])->count();
        $demandesRefusees  = Demande::whereIn('statut', ['refusée', 'refusee'])->count();

        return view('admin.dashboard', compact(
            'demandes',
            'totalDemandes',
            'demandesEnAttente',
            'demandesAcceptees',
            'demandesRefusees'
        ));
    }
    /**
     * Mettre à jour le prix des services.
     */
    public function updatePrices(Request $request)
    {
        $request->validate([
            'tarifs' => 'required|array',
            'tarifs.*' => 'required|numeric|min:0',
        ]);
        // Exemple si vous sauvegardez dans la table `type_actes` ou `services` :
        foreach ($request->tarifs as $serviceKey => $prix) {
            // Ajustez le nom de votre modèle et colonne selon votre base de données
            // Par exemple si vous avez un modèle TypeActe :
            \App\Models\TypeActe::where('type_acte', $serviceKey)
                ->update(['prix_unitaire' => $prix]);
        }

        return redirect()->back()->with('success', 'Les tarifs des services ont été mis à jour avec succès.');
    }

    /**
     * Dashboard propre au Super Admin
     */
    public function superAdminIndex()
    {
        // ✅ Statistiques
        $totalAdmins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->count();
        $totalDemandes = \App\Models\Demande::count();
        $demandesEnAttente = \App\Models\Demande::whereIn('statut', ['en_attente', 'en attente'])->count();
        $demandesAcceptees = \App\Models\Demande::whereIn('statut', ['acceptée', 'acceptee'])->count();
        $demandesRefusees  = \App\Models\Demande::whereIn('statut', ['refusée', 'refusee'])->count();

        // ✅ Pagination (au lieu de ->get())
        $demandes = \App\Models\Demande::with(['citoyen', 'demandeActes.typeActe'])
            ->latest()
            ->paginate(10);   // ⬅️ OBLIGATOIRE pour avoir ->total() dans la vue

        return view('super-admin.dashboard', compact(
            'totalAdmins',
            'totalDemandes',
            'demandesEnAttente',
            'demandesAcceptees',
            'demandesRefusees',
            'demandes'
        ));
    }

    // statistiques
    public function statistiques()
    {
        // Compteurs globaux
        $stats = [
            'total' => \App\Models\Demande::count(),
            'en_attente' => \App\Models\Demande::whereIn('statut', ['en-attente', 'en attente'])->count(),
            'acceptee'   => \App\Models\Demande::whereIn('statut', ['acceptée', 'acceptee'])->count(),
            'refusee' => \App\Models\Demande::whereIn('statut', ['refusée', 'refusee'])->count(),
        ];

        // ✅ Répartition par statut
        $parStatut = [
            ['label' => 'Acceptées',  'value' => $stats['acceptee'],   'color' => '#10B981'],
            ['label' => 'Refusées',   'value' => $stats['refusee'],    'color' => '#EF4444'],
            ['label' => 'En attente', 'value' => $stats['en_attente'], 'color' => '#F59E0B'],
        ];

        // Demandes par 12mois
         $parMois = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Demande::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $parMois[] = [
                'label' => $date->translatedFormat('M') . ' ' . $date->format('y'),
                'total' => $count,
            ];
        }

         /* ═══════════════════════════════════════════════════════
       30 DERNIERS JOURS
        ═══════════════════════════════════════════════════════ */
        $par30Jours = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Demande::whereDate('created_at', $date->toDateString())->count();

            $par30Jours[] = [
                'label' => $date->format('d/m'),
                'total' => $count,
            ];
        }

        /* ═══════════════════════════════════════════════════════
       7 DERNIERS JOURS
        ═══════════════════════════════════════════════════════ */
        $par7Jours = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Demande::whereDate('created_at', $date->toDateString())->count();

            $par7Jours[] = [
                 'label' => $date->translatedFormat('D d/m'),
                'total' => $count,
            ];
        }

        /* ═══════════════════════════════════════════════════════
       24 DERNIÈRES HEURES
        ═══════════════════════════════════════════════════════ */
        $par24Heures = [];
        for ($i = 23; $i >= 0; $i--) {
            $heure = now()->subHours($i);
            $count = Demande::whereDate('created_at', $heure->toDateString())
                ->whereRaw('HOUR(created_at) = ?', [$heure->hour])
                ->count();

             $par24Heures[] = [
                'label' => $heure->format('H') . 'h',
                'total' => $count,
            ];
        }
        return view('super-admin.statistiques', compact('stats', 'parStatut', 'parMois', 'par30Jours', 'par7Jours', 'par24Heures' ));
    }
}
