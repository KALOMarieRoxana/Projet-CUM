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
        // Initialisation du Query Builder avec Eager Loading
        $query = Demande::with([
            'citoyen',
            'demandeActes.typeActe',
            'demandeActes.acte'
        ]); // <-- Point-virgule ajouté ici pour corriger l'erreur de syntaxe

        // Filtrage par statut si demandé dans l'URL
        if ($request->has('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        $demandes = $query->latest()->get();

        // Statistiques globales
        $totalDemandes     = Demande::count();
        $demandesEnAttente = Demande::enAttente()->count();
        $demandesAcceptees = Demande::acceptee()->count();
        $demandesRefusees  = Demande::refusee()->count();

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
        $totalAdmins = \App\Models\User::where('role', 'admin')->count();
        $totalDemandes = \App\Models\Demande::count();
        $demandesEnAttente = \App\Models\Demande::where('statut', 'en_attente')->count();
        $demandesAcceptees = \App\Models\Demande::where('statut', 'acceptée')->count();
        $demandesRefusees = \App\Models\Demande::where('statut', 'refusée')->count();

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
}
