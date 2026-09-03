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
        $query = Demande::with('user');

        if ($request->has('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        $demandes = $query->latest()->get();

        $totalDemandes = Demande::count();
        $demandesEnAttente = Demande::where('statut', 'en_attente')->count();
        $demandesAcceptees = Demande::where('statut', 'acceptee')->count();
        $demandesRefusees = Demande::where('statut', 'refusee')->count();

        return view('admin.dashboard', compact(
            'demandes',
            'totalDemandes',
            'demandesEnAttente',
            'demandesAcceptees',
            'demandesRefusees'
        ));
    }

    /**
     * Dashboard propre au Super Admin
     */
    public function superAdminIndex(Request $request)
    {
        // Statistiques globales du système
        $totalAdmins = User::where('role', 'admin')->count();
        $totalDemandes = Demande::count();
        $demandesEnAttente = Demande::where('statut', 'en_attente')->count();
        $demandesAcceptees = Demande::where('statut', 'acceptee')->count();

        // Récupération des derniers admins et des dernières demandes
        $admins = User::where('role', 'admin')->latest()->take(5)->get();
        $demandes = Demande::with('user')->latest()->take(5)->get();

        return view('super-admin.dashboard', compact(
            'totalAdmins',
            'totalDemandes',
            'demandesEnAttente',
            'demandesAcceptees',
            'admins',
            'demandes'
        ));
    }
}