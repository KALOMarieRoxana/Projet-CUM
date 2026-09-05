<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDemandeRequest;
use App\Models\Demande;
use App\Models\DemandeActe;
use App\Models\Naissance;
use App\Models\Mariage;
use App\Models\Deces;
use App\Models\Divorce;
use App\Models\TypeActe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        // Adaptation : On charge les relations 'citoyen' et 'demandeActes'
        $query = Demande::with(['citoyen', 'demandeActes.acte', 'traiteur']);

        // Filtrage par statut si passé en paramètre
        if ($request->filled('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        $demandes = $query->latest()->paginate(15);

        if ($request->wantsJson()) {
            return response()->json(['demandes' => $demandes], 200);
        }

        return view('admin.demandes', compact('demandes'));
    }

    /**
     * Met à jour le statut d'une demande (Accepter / Refuser).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|string|in:en_attente,acceptée,refusée,partiellement_acceptée',
            'commentaire_admin' => 'nullable|string',
        ]);

        $demande = Demande::findOrFail($id);
        
        $demande->update([
            'statut'            => $request->statut,
            'commentaire_admin' => $request->commentaire_admin,
            'traite_par'        => Auth::id(),
            'date_traitement'   => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Statut mis à jour avec succès.',
                'demande' => $demande->load('traiteur')
            ], 200);
        }

        return redirect()->back()->with('success', 'Le statut de la demande a été mis à jour.');
    }


    // ==========================================
    // PARTIE API (Utilisateurs / Client Mobile/Front)
    // ==========================================

    /**
     * Créer une nouvelle demande avec enregistrement dans demande_actes
     */
    public function store(StoreDemandeRequest $request)
    {
        try {
            $citoyen = Auth::user();

            if (!$citoyen) {
                return response()->json([
                    'message' => 'Utilisateur non authentifié.'
                ], 401);
            }

            Log::info('Citoyen connecté:', ['citoyen_id' => $citoyen->id, 'email' => $citoyen->email]);

            return DB::transaction(function () use ($request, $citoyen) {
                
                // 1. Génération du numéro de référence unique
                $reference = Demande::generateReference();

                // 2. Création de la Demande globale
                $demande = Demande::create([
                    'reference'               => $reference,
                    'citoyen_id'              => $citoyen->id,
                    'demandeur_nom'           => $request->demandeur_nom,
                    'demandeur_prenom'        => $request->demandeur_prenom,
                    'demandeur_adresse'       => $request->demandeur_adresse,
                    'demandeur_relation'      => $request->demandeur_relation,
                    'demandeur_contact'       => $request->demandeur_contact,
                    'personne_nom'            => $request->personne_nom,
                    'personne_prenom'         => $request->personne_prenom,
                    'personne_lieu_naissance' => $request->personne_lieu_naissance,
                    'personne_date_naissance' => $request->personne_date_naissance,
                    'service'                 => $request->service ?? 'standard',
                    'prix_total'              => 0,
                    'nombre_actes'            => 0,
                    'statut'                  => 'en_attente',
                ]);

                // 3. Traitement des actes envoyés (tableau 'actes' ou acte unique)
                $actesInput = $request->has('actes') ? $request->actes : [$request->all()];
                
                $prixTotalGlobal = 0;
                $totalNombreActes = 0;

                foreach ($actesInput as $item) {
                    $typeActeNom = $item['type_acte'] ?? 'naissance';
                    
                    $typeActe = TypeActe::where('type_acte', $typeActeNom)->firstOrFail();

                    // Calcul automatique du tarif selon la langue et le service
                    $langue = $item['langue'] ?? 'FR';
                    $service = $item['service'] ?? $request->service ?? 'standard';
                    
                    $prixUnitaire = $typeActe->getPrixUnitaire($langue, $service);
                    $quantite = $item['nbre_com'] ?? $item['quantite'] ?? 1;
                    $sousTotal = $prixUnitaire * $quantite;

                    // Champs communs aux 4 tables d'actes
                    $commonData = [
                        'langue'           => $langue,
                        'type_service'     => $service,
                        'sigle'            => $typeActe->sigle ?? 'AN',
                        'montantExpressMG' => $typeActe->montantExpressMG ?? 0,
                        'montantStandardMG'  => $typeActe->montantStandardMG ?? 0,
                        'montantStandardFR'  => $typeActe->montantStandardFR ?? 0,
                        'montantExpressFR' => $typeActe->montantExpressFR ?? 0,
                        'nbre_com'         => $quantite,
                        'num_acte'         => $item['num_acte'] ?? $request->personne_numero_acte ?? null,
                    ];

                    $acteModel = null;

                    // Instanciation selon le type d'acte
                    switch ($typeActeNom) {
                        case 'naissance':
                            $acteModel = Naissance::create(array_merge($commonData, [
                                'nom'        => $request->personne_nom ?? $item['nom'] ?? null,
                                'prenom'     => $request->personne_prenom ?? $item['prenom'] ?? null,
                                'date_naiss' => $request->personne_date_naissance ?? $item['date_naiss'] ?? null,
                                'lieu_naiss' => $request->personne_lieu_naissance ?? $item['lieu_naiss'] ?? null,
                                'nom_pere'   => $item['nom_pere'] ?? null,
                                'prenom_pere'=> $item['prenom_pere'] ?? null,
                                'nom_mere'   => $item['nom_mere'] ?? null,
                                'prenom_mere'=> $item['prenom_mere'] ?? null,
                            ]));
                            break;

                            case 'mariage':
                            $acteModel = Mariage::create(array_merge($commonData, [
                                'nom_epoux'         => $item['nom_epoux'] ?? null,
                                'prenom_epoux'      => $item['prenom_epoux'] ?? null,
                                'date_naiss_epoux'  => $item['date_naiss_epoux'] ?? null,
                                'lieu_naiss_epoux'  => $item['lieu_naiss_epoux'] ?? null,
                                'nom_epouse'        => $item['nom_epouse'] ?? null,
                                'prenom_epouse'     => $item['prenom_epouse'] ?? null,
                                'date_naiss_epouse' => $item['date_naiss_epouse'] ?? null,
                                'lieu_naiss_epouse' => $item['lieu_naiss_epouse'] ?? null,
                                'date_mariage'      => $item['date_mariage'] ?? null,
                                'lieu_mariage'      => $item['lieu_mariage'] ?? null,
                            ]));
                            break;

                            case 'divorce':
                            $acteModel = Divorce::create(array_merge($commonData, [
                                'nom_epoux'     => $item['nom_epoux'] ?? null,
                                'prenom_epoux'  => $item['prenom_epoux'] ?? null,
                                'nom_epouse'    => $item['nom_epouse'] ?? null,
                                'prenom_epouse' => $item['prenom_epouse'] ?? null,
                                'date_jugement' => $item['date_jugement'] ?? null,
                                'num_jugement'  => $item['num_jugement'] ?? null,
                                'tribunal'      => $item['tribunal'] ?? null,
                            ]));
                            break;

                            case 'deces':
                            $acteModel = Deces::create(array_merge($commonData, [
                                'nom_defunt'        => $item['nom_defunt'] ?? null,
                                'prenom_defunt'     => $item['prenom_defunt'] ?? null,
                                'date_naiss_defunt' => $item['date_naiss_defunt'] ?? null,
                                'date_deces'        => $item['date_deces'] ?? null,
                                'lieu_deces'        => $item['lieu_deces'] ?? null,
                            ]));
                            break;
                    }
                    
                    // Création de la ligne dans demande_actes
                    DemandeActe::create([
                        'demande_id'    => $demande->id_demande,
                        'type_acte'     => $typeActeNom, // Clé textuelle
                        'acte_type'     => Naissance::class,
                        'acte_id'       => $naissance->id,
                        'prix_unitaire' => $prixUnitaire,
                        'quantite'      => $quantite,
                        'sous_total'    => $sousTotal,
                        'statut'        => 'en_attente',
                    ]);

                    $prixTotalGlobal += $sousTotal;
                    $totalNombreActes += $quantite;
                }

                // 4. Mise à jour du total de la demande
                $demande->update([
                    'prix_total'   => $prixTotalGlobal,
                    'nombre_actes' => $totalNombreActes,
                ]);

                return response()->json([
                    'message' => 'Demande créée avec succès.',
                    'demande' => $demande->load('demandeActes.acte')
                ], 201);
            });

        } catch (\Exception $e) {
            Log::error('Erreur store demande: ' . $e->getMessage());
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
                ->with(['demandeActes.acte', 'demandeActes.typeActeRelation'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'demandes' => $demandes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des demandes: ' . $e->getMessage()
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

            // Si c'est un administrateur/agent
            if ($user && in_array($user->role, ['admin', 'super_admin', 'agent'])) {
                $demande = Demande::with(['citoyen', 'traiteur', 'demandeActes.acte', 'demandeActes.typeActeRelation'])->findOrFail($id);
            } else {
                // Si c'est le citoyen lui-même
                $demande = Demande::where('citoyen_id', $user->id)
                    ->with(['demandeActes.acte', 'demandeActes.typeActeRelation'])
                    ->where('id_demande', $id)
                    ->firstOrFail();
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
            $demande = Demande::where('citoyen_id', $user->id)
                ->where('id_demande', $id)
                ->firstOrFail();

            if ($demande->statut !== 'en_attente') {
                return response()->json([
                    'message' => 'Seules les demandes en attente peuvent être annulées.'
                ], 400);
            }

            // Suppression en cascade (supprime aussi demande_actes grâce aux clés étrangères)
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