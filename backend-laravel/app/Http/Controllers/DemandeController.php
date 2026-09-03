<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDemandeRequest;
use App\Models\Demande;
use App\Models\TypeActe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DemandeController extends Controller
{
    // ==========================================
    // PARTIE WEB (Tableau de Bord / Back-Office)
    // ==========================================

    /**
     * Affiche la liste des demandes dans l'espace administration.
     */
    public function index(Request $request)
    {
        $query = Demande::with(['user', 'typeActe']);

        // Filtrage par statut si passé en paramètre
        if ($request->filled('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        $demandes = $query->latest()->paginate(15);

        // Si la requête attend du JSON (API)
        if ($request->wantsJson()) {
            return response()->json(['demandes' => $demandes], 200);
        }

        // Sinon retour de la vue Blade
        return view('admin.demandes', compact('demandes'));
    }

    /**
     * Met à jour le statut d'une demande (Accepter / Refuser).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|string',
        ]);

        $demande = Demande::findOrFail($id);
        $demande->update([
            'statut' => $request->statut,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Statut mis à jour avec succès.',
                'demande' => $demande
            ], 200);
        }

        return redirect()->back()->with('success', 'Le statut de la demande a été mis à jour.');
    }


    // ==========================================
    // PARTIE API (Utilisateurs / Client Mobile/Front)
    // ==========================================

    /**
     * Créer une nouvelle demande
     */
    public function store(StoreDemandeRequest $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Utilisateur non authentifié.'
                ], 401);
            }

            Log::info('Utilisateur connecté:', ['user_id' => $user->id, 'email' => $user->email]);

            $typeActe = TypeActe::where('type_acte', $request->type_acte)->first();
            if (!$typeActe) {
                return response()->json([
                    'message' => 'Type d\'acte invalide.'
                ], 400);
            }

            $prix = $request->service === 'express' 
                ? $typeActe->prix_express 
                : $typeActe->prix_standard;

            $demande = Demande::create([
                'id_citoyen' => $citoyen->id,
                'type_acte_id' => $typeActe->id,
                'demandeur_nom' => $request->demandeur_nom,
                'demandeur_prenom' => $request->demandeur_prenom,
                'demandeur_adresse' => $request->demandeur_adresse,
                'demandeur_relation' => $request->demandeur_relation,
                'demandeur_contact' => $request->demandeur_contact,
                'personne_nom' => $request->personne_nom,
                'personne_prenom' => $request->personne_prenom,
                'personne_numero_acte' => $request->personne_numero_acte,
                'personne_lieu_naissance' => $request->personne_lieu_naissance,
                'personne_date_naissance' => $request->personne_date_naissance,
                'service' => $request->service,
                'prix' => $prix,
                'statut' => 'en attente',
            ]);

            $demande->load('typeActe');

            return response()->json([
                'message' => 'Demande créée avec succès.',
                'demande' => $demande
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la demande: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer toutes les demandes de l'utilisateur connecté
     */
    public function mesDemandes(Request $request)
    {
        try {
            $citoyen = $request->user();

            if (!$citoyen) {
                return response()->json([
                    'message' => 'Citoyen non authentifié.'
                ], 401);
            }

            $demandes = Demande::where('citoyen_id', $citoyen->id)
                ->with('typeActe')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'demandes' => $demandes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des demandes.'
            ], 500);
        }
    }

    /**
     * Récupérer une demande spécifique
     */
    public function show($id)
    {
        try {
            $user = Auth::user();

            // Si c'est un administrateur, il peut voir n'importe quelle demande
            if ($user && ($user->role === 'admin' || $user->role === 'super_admin')) {
                $demande = Demande::with(['typeActe', 'user'])->findOrFail($id);
            } else {
                // Sinon l'utilisateur ne peut voir que sa propre demande
                $demande = Demande::where('user_id', $user->id)
                    ->with('typeActe')
                    ->findOrFail($id);
            }

            if (request()->wantsJson()) {
                return response()->json(['demande' => $demande], 200);
            }

            return view('admin.demandes_show', compact('demande'));

        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Demande non trouvée.'], 404);
            }
            return redirect()->back()->with('error', 'Demande non trouvée.');
        }
    }

    /**
     * Annuler une demande (seulement si elle est en attente)
     */
    public function annuler($id)
    {
        try {
            $user = Auth::user();
            $demande = Demande::where('user_id', $user->id)->findOrFail($id);

            if ($demande->statut !== 'en attente') {
                return response()->json([
                    'message' => 'Seules les demandes en attente peuvent être annulées.'
                ], 400);
            }

            $demande->delete();

            return response()->json([
                'message' => 'Demande annulée avec succès.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'annulation de la demande.'
            ], 500);
        }
    }
}