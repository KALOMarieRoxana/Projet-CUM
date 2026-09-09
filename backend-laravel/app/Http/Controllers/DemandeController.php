<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $query = Demande::with(['citoyen', 'demandeActes.acte', 'traiteur']);

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
                'demande' => $demande->load('traitePar')
            ], 200);
        }

        return redirect()->back()->with('success', 'Le statut de la demande a été mis à jour.');
    }

    // ==========================================
    // PARTIE API (Utilisateurs / Client Mobile/Front)
    // ==========================================

    /**
     * Enregistrer une nouvelle demande d'acte(s)
     */
    public function store(Request $request)
    {
        try {
            $citoyen = Auth::user();

            if (!$citoyen) {
                return response()->json([
                    'message' => 'Utilisateur non authentifié.'
                ], 401);
            }

            // Récupération sécurisée de l'ID citoyen
            $citoyenId = $citoyen->id_citoyens ?? $citoyen->id;

            Log::info('Citoyen connecté:', ['citoyen_id' => $citoyenId, 'email' => $citoyen->email]);

            return DB::transaction(function () use ($request, $citoyen, $citoyenId) {
                
                // 1. Génération du numéro de référence unique
                $reference = 'DEM-' . date('Ymd') . '-' . strtoupper(uniqid());

                // 2. Création de la Demande globale
                $demande = Demande::create([
                    'reference'               => $reference,
                    'citoyen_id'              => $citoyenId,
                    'demandeur_nom'           => $request->demandeur_nom ?? '',
                    'demandeur_prenom'        => $request->demandeur_prenom ?? '',
                    'demandeur_adresse'       => $request->demandeur_adresse ?? '',
                    'demandeur_relation'      => $request->demandeur_relation ?? '',
                    'demandeur_contact'       => $request->demandeur_contact ?? '',
                    'personne_nom'            => $request->personne_nom ?? '',
                    'personne_prenom'         => $request->personne_prenom ?? '',
                    'personne_lieu_naissance' => $request->personne_lieu_naissance ?? '',
                    'personne_date_naissance' => $request->personne_date_naissance ?? null,
                    'personne_numero_acte'    => $request->personne_numero_acte ?? null,
                    'service'                 => $request->service ?? 'standard',
                    'prix_total'              => 0,
                    'nombre_actes'            => 0,
                    'statut'                  => 'en_attente',
                ]);

                // 3. Traitement des actes envoyés
                // Si le format est 'actes' (tableau) ou 'demandes' (ancien format)
                $actesInput = [];
                if ($request->has('actes')) {
                    $actesInput = $request->actes;
                } elseif ($request->has('demandes')) {
                    $actesInput = $request->demandes;
                } else {
                    // Format unique (une seule demande)
                    $actesInput = [$request->all()];
                }

                // Si actesInput est un objet, le convertir en tableau
                if (is_object($actesInput)) {
                    $actesInput = $actesInput->toArray();
                }

                $prixTotalGlobal = 0;
                $totalNombreActes = 0;

                foreach ($actesInput as $item) {
                    // Si item est un objet, le convertir en tableau
                    if (is_object($item)) {
                        $item = $item->toArray();
                    }

                    // Récupération du type d'acte
                    $typeActeNom = $item['type_acte'] ?? 'naissance';
                    
                    // Récupération de la référence des tarifs pour ce type d'acte
                    $typeActe = TypeActe::where('type_acte', $typeActeNom)->first();

                    if (!$typeActe) {
                        throw new \Exception("Type d'acte non trouvé: {$typeActeNom}");
                    }

                    // Langue (FR ou MG) et Service (standard ou express)
                    $langue = strtoupper($item['langue'] ?? $request->langue ?? 'MG');
                    $service = strtolower($item['service'] ?? $request->service ?? 'standard');

                    // Calcul du prix unitaire via la méthode du modèle TypeActe
                    $prixUnitaire = $typeActe->calculerPrix($langue, $service);

                    $quantite = $item['quantite'] ?? $item['nbre_com'] ?? 1;
                    $sousTotal = $prixUnitaire * $quantite;

                    // Champs communs insérés dans les tables spécifiques d'actes
                    $commonData = [
                        'langue'            => $langue,
                        'type_service'      => $service,
                        'sigle'             => $typeActe->sigle ?? 'AN',
                        'montantExpressMG'  => $typeActe->montantExpressMG ?? 0,
                        'montantStandardMG' => $typeActe->montantStandardMG ?? 0,
                        'montantStandardFR' => $typeActe->montantStandardFR ?? 0,
                        'montantExpressFR'  => $typeActe->montantExpressFR ?? 0,
                        'nbre_com'          => $quantite,
                        'num_acte'          => $item['num_acte'] ?? $request->personne_numero_acte ?? null,
                    ];

                    $acteModel = null;
                    $details = $item['details'] ?? $item;

                    // Instanciation de l'acte selon son type
                    switch ($typeActeNom) {
                        case 'naissance':
                            $acteModel = Naissance::create(array_merge($commonData, [
                                'nom'            => $details['personne_nom'] ?? $item['nom'] ?? $request->personne_nom ?? '',
                                'prenom'         => $details['personne_prenom'] ?? $item['prenom'] ?? $request->personne_prenom ?? '',
                                'date_naissance' => $details['personne_date_naissance'] ?? $item['date_naissance'] ?? $request->personne_date_naissance ?? null,
                                'lieu_naissance' => $details['personne_lieu_naissance'] ?? $item['lieu_naissance'] ?? $request->personne_lieu_naissance ?? '',
                                'nom_pere'       => $details['pere_nom'] ?? $item['nom_pere'] ?? $request->nom_pere ?? '',
                                'prenom_pere'    => $details['pere_prenom'] ?? $item['prenom_pere'] ?? $request->prenom_pere ?? '',
                                'nom_mere'       => $details['mere_nom'] ?? $item['nom_mere'] ?? $request->nom_mere ?? '',
                                'prenom_mere'    => $details['mere_prenom'] ?? $item['prenom_mere'] ?? $request->prenom_mere ?? '',
                                'date_acte'      => now(),
                            ]));
                            break;

                        case 'mariage':
                            $acteModel = Mariage::create(array_merge($commonData, [
                                'nom_epoux'             => $details['epoux_nom'] ?? $item['nom_epoux'] ?? '',
                                'prenom_epoux'          => $details['epoux_prenom'] ?? $item['prenom_epoux'] ?? '',
                                'date_naissance_epoux'  => $details['epoux_date_naissance'] ?? $item['date_naissance_epoux'] ?? null,
                                'lieu_naissance_epoux'  => $details['epoux_lieu_naissance'] ?? $item['lieu_naissance_epoux'] ?? '',
                                'nom_epouse'            => $details['epouse_nom'] ?? $item['nom_epouse'] ?? '',
                                'prenom_epouse'         => $details['epouse_prenom'] ?? $item['prenom_epouse'] ?? '',
                                'date_naissance_epouse' => $details['epouse_date_naissance'] ?? $item['date_naissance_epouse'] ?? null,
                                'lieu_naissance_epouse' => $details['epouse_lieu_naissance'] ?? $item['lieu_naissance_epouse'] ?? '',
                                'date_mariage'          => $details['date_mariage'] ?? $item['date_mariage'] ?? null,
                                'lieu_mariage'          => $details['lieu_mariage'] ?? $item['lieu_mariage'] ?? '',
                            ]));
                            break;

                        case 'deces':
                            $acteModel = Deces::create(array_merge($commonData, [
                                'nom_defunt'            => $details['defunt_nom'] ?? $item['nom_defunt'] ?? '',
                                'prenom_defunt'         => $details['defunt_prenom'] ?? $item['prenom_defunt'] ?? '',
                                'date_naissance_defunt' => $details['defunt_date_naissance'] ?? $item['date_naissance_defunt'] ?? null,
                                'date_deces'            => $details['date_deces'] ?? $item['date_deces'] ?? null,
                                'lieu_deces'            => $details['lieu_deces'] ?? $item['lieu_deces'] ?? '',
                            ]));
                            break;

                        case 'divorces':
                            $acteModel = Divorce::create(array_merge($commonData, [
                                'nom_epoux'     => $details['conjoint_nom'] ?? $item['nom_epoux'] ?? '',
                                'prenom_epoux'  => $details['conjoint_prenom'] ?? $item['prenom_epoux'] ?? '',
                                'nom_epouse'    => $details['conjointe_nom'] ?? $item['nom_epouse'] ?? '',
                                'prenom_epouse' => $details['conjointe_prenom'] ?? $item['prenom_epouse'] ?? '',
                                'date_jugement' => $details['date_demande_divorce'] ?? $item['date_jugement'] ?? null,
                                'num_jugement'  => $details['num_jugement'] ?? $item['num_jugement'] ?? '',
                                'tribunal'      => $details['tribunal'] ?? $item['tribunal'] ?? '',
                            ]));
                            break;

                        default:
                            throw new \Exception("Type d'acte non reconnu: {$typeActeNom}");
                    }

                    // Enregistrement de la ligne pivot polymorphique dans demande_actes
                    if ($acteModel) {
                        DemandeActe::create([
                            'demande_id'    => $demande->id_demande ?? $demande->id,
                            'type_acte_id'  => $typeActe->id,
                            'acte_type'     => get_class($acteModel),
                            'acte_id'       => $acteModel->getKey(),
                            'prix_unitaire' => $prixUnitaire,
                            'quantite'      => $quantite,
                            'sous_total'    => $sousTotal,
                            'statut'        => 'en_attente',
                        ]);
                    }

                    $prixTotalGlobal += $sousTotal;
                    $totalNombreActes += $quantite;
                }

                // 4. Mise à jour des montants cumulés dans la demande
                $demande->update([
                    'prix_total'   => $prixTotalGlobal,
                    'nombre_actes' => $totalNombreActes,
                ]);

                return response()->json([
                    'message' => 'Demande enregistrée avec succès.',
                    'reference' => $reference,
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

            $citoyenId = $citoyen->id_citoyens ?? $citoyen->id;

            $demandes = Demande::where('citoyen_id', $citoyenId)
                ->with(['demandeActes.typeActe', 'demandeActes.acte'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'demandes' => $demandes
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur mesDemandes: ' . $e->getMessage());
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

            if (!$user) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }

            $userId = $user->id_citoyens ?? $user->id;

            // Si l'utilisateur est admin, il peut voir toutes les demandes
            if (in_array($user->role ?? '', ['admin', 'super_admin', 'agent'])) {
                $demande = Demande::with(['citoyen', 'traitePar', 'demandeActes.typeActe', 'demandeActes.acte'])
                    ->findOrFail($id);
            } else {
                // Sinon, seulement ses propres demandes
                $demande = Demande::where('citoyen_id', $userId)
                    ->with(['demandeActes.typeActe', 'demandeActes.acte'])
                    ->findOrFail($id);
            }

            if (request()->wantsJson()) {
                return response()->json(['demande' => $demande], 200);
            }

            return view('admin.demandes_show', compact('demande'));

        } catch (\Exception $e) {
            Log::error('Erreur show demande: ' . $e->getMessage());
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

            if (!$user) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }

            $userId = $user->id_citoyens ?? $user->id;

            $demande = Demande::where('citoyen_id', $userId)
                ->findOrFail($id);

            if ($demande->statut !== 'en_attente') {
                return response()->json([
                    'message' => 'Seules les demandes en attente peuvent être annulées.'
                ], 400);
            }

            $demande->delete();

            return response()->json([
                'message' => 'Demande annulée avec succès.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur annuler demande: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'annulation de la demande.'
            ], 500);
        }
    }
}