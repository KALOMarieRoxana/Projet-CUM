<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDemandeRequest;
use App\Models\Demande;
use App\Models\TypeActe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemandeController extends Controller
{
    /**
     * Créer une nouvelle demande
     */
    public function store(StoreDemandeRequest $request)
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Utilisateur non authentifié.'
                ], 401);
            }
            // Log pour deboguer
              \Log::info('Utilisateur connecté:', ['user_id' => $user->id, 'email' => $user->email]);
        

            // Récupérer le type d'acte
            $typeActe = TypeActe::where('type_acte', $request->type_acte)->first();
            if (!$typeActe) {
                return response()->json([
                    'message' => 'Type d\'acte invalide.'
                ], 400);
            }

            // Calculer le prix en fonction du service
            $prix = $request->service === 'express' 
                ? $typeActe->prix_express 
                : $typeActe->prix_standard;

            // Créer la demande
            $demande = Demande::create([
                'user_id' => $user->id,
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

            // Charger la relation typeActe pour la réponse
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
            $user = Auth::user();
            $demandes = Demande::where('user_id', $user->id)
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
            $demande = Demande::where('user_id', $user->id)
                ->with('typeActe')
                ->findOrFail($id);

            return response()->json([
                'demande' => $demande
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Demande non trouvée.'
            ], 404);
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