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

        if ($request->filled('type') && $request->type !== 'tous') {
            $query->whereHas('demandeActes.typeActe', function ($q) use ($request) {
                $q->where('type_acte', $request->type);
            });
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('reference', 'LIKE', "%{$q}%")
                    ->orWhere('demandeur_nom', 'LIKE', "%{$q}%")
                    ->orWhere('demandeur_prenom', 'LIKE', "%{$q}%");
            });
        }

        $demandes = $query->latest()->paginate(15);

        // ✅ 2. Compteurs GLOBAUX (indépendants des filtres)
        $stats = [
            'total'      => Demande::count(),
            'en_attente' => Demande::whereIn('statut', ['en_attente', 'en attente'])->count(),
            'acceptee'   => Demande::whereIn('statut', ['acceptée', 'acceptee'])->count(),
            'refusee'    => Demande::whereIn('statut', ['refusée', 'refusee'])->count(),
        ];

         // ✅ 3. Choisir la vue selon la route appelée
        if ($request->routeIs('super-admin.*')) {
            return view('super-admin.demandes', compact('demandes', 'stats'));
        }

        return view('admin.demandes', compact('demandes', 'stats'));
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

        // ✅ AJOUTER CETTE LIGNE :
        $ancienStatut = $demande->statut;
        
        $demande->update([
            'statut'            => $request->statut,
            'commentaire_admin' => $request->commentaire_admin,
            'traite_par'        => Auth::id(),
            'date_traitement'   => now(),
        ]);

        // ✅ GÉNÉRER LE PDF SI ACCEPTÉE
        if ($request->statut === 'acceptée' && $ancienStatut !== 'acceptée') {
            try {
                $pdfService = new \App\Services\PdfService();
                $filename = $pdfService->genererPdf($demande);
            
                \Log::info('✅ PDF généré:', [
                    'reference' => $demande->reference,
                    'filename' => $filename,
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ Erreur génération PDF: ' . $e->getMessage());
            }
        }

        // ✅ 2. ENVOYER LA NOTIFICATION AU CITOYEN
        try {
            $citoyen = $demande->citoyen;
        
            if ($citoyen) {
                if ($request->statut === 'acceptée') {
                    $citoyen->notify(new \App\Notifications\DemandeAccepteeNotification($demande->fresh()));
                    \Log::info('📤 Notification ACCEPTÉE envoyée: ' . $demande->reference);
                } elseif ($request->statut === 'refusée') {
                    $citoyen->notify(new \App\Notifications\DemandeRefuseeNotification($demande->fresh()));
                    \Log::info('📤 Notification REFUSÉE envoyée: ' . $demande->reference);
                }
            }
        } catch (\Exception $e) {
            \Log::error('❌ Erreur notification: ' . $e->getMessage());
        }

        
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
    public function storeGroupe(Request $request)
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
                $reference = strtoupper(substr(uniqid(), 0, 6));

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
                    $typeActeNom = strtolower(
                        $item['type_acte']
                        ?? ($item['details']['type_acte'] ?? null)
                        ?? $request->type_acte
                        ?? 'naissance'
                    );
                    
                    $typeActe = TypeActe::where('type_acte', $typeActeNom)->first();
                    // Récupération de la référence des tarifs pour ce type d'acte

                    if (!$typeActe) {
                        throw new \Exception("Type d'acte non trouvé: {$typeActeNom}");
                    }

                    // Langue (FR ou MG) et Service (standard ou express)
                    $langue = strtoupper($item['langue'] ?? $request->langue ?? 'MG');
                    $service = strtolower($item['service'] ?? $request->service ?? 'standard');

                    // Calcul du prix unitaire via la méthode du modèle TypeActe
                    $prixUnitaire = $typeActe->calculerPrix($langue, $service);

                    $quantite = $item['quantite'] ?? $item['nbre_com'] ?? 1;

                    // ✅ Récupérer le supplément
                    $supplementId = $item['supplement_id'] ?? null;
                    $quantiteSupplement = intval($item['quantite_supplement'] ?? 0);
                    $prixActe = floatval($item['prix_acte'] ?? 0);
                    if ($prixActe <= 0) {
                        $prixActe = floatval($prixUnitaire);
                    }
                    $prixSupplement = 0;

                    // Calculer le prix du suplement si sélectionné
                    if ($supplementId) {
                        $supplement = \App\Models\TypeActeSupplement::find($supplementId);
                        if ($supplement) {
                            $serviceLower = strtolower($service);
                            $langueLower = strtolower($langue);

                            $champPrix = "prix_{$serviceLower}_{$langueLower}";
                            $prixSupplement = floatval($supplement->$champPrix ?? 0);

                              // ✅ Debug
                            \Log::info('Calcul prix supplément:', [
                                'supplement_id' => $supplementId,
                                'champ' => $champPrix,
                                'valeur' => $supplement->$champPrix,
                                'prixSupplement' => $prixSupplement,
                            ]);
                        }
                    }
                    // Sous total = (prix acte * qté) + (prix supplément * qt supplément)
                    $sousTotal = ($prixActe * $quantite) + ($prixSupplement * $quantiteSupplement);

                     // ✅ Log pour vérifier
                    \Log::info('📊 Calcul ligne:', [
                        'type_acte' => $typeActeNom,
                        'prixActe' => $prixActe,
                        'quantite' => $quantite,
                        'prixSupplement' => $prixSupplement,
                        'quantiteSupplement' => $quantiteSupplement,
                        'sousTotal' => $sousTotal,
                    ]);

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
                        'num_acte'          => $item['num_acte'] ?? $request->personne_numero_acte ?? $reference,
                    ];

                    $acteModel = null;
                    $details = $item['details'] ?? $item;

                    // Instanciation de l'acte selon son type avec recherche approfondie des clés
                    switch (strtolower($typeActeNom)) {
                        case 'naissance':
                            $acteModel = Naissance::create(array_merge($commonData, [
                                'nom'            => $details['nom'] ?? $details['personne_nom'] ?? $item['nom'] ?? $request->personne_nom ?? '',
                                'prenom'         => $details['prenom'] ?? $details['personne_prenom'] ?? $item['prenom'] ?? $request->personne_prenom ?? '',
                                'date_naissance' => $details['date_naissance'] ?? $details['personne_date_naissance'] ?? $item['date_naissance'] ?? $request->personne_date_naissance ?? null,
                                'lieu_naissance' => $details['lieu_naissance'] ?? $details['personne_lieu_naissance'] ?? $item['lieu_naissance'] ?? $request->personne_lieu_naissance ?? '',
                                'nom_pere'       => $details['nom_pere'] ?? $details['pere_nom'] ?? $item['nom_pere'] ?? $request->nom_pere ?? '',
                                'prenom_pere'    => $details['prenom_pere'] ?? $details['pere_prenom'] ?? $item['prenom_pere'] ?? $request->prenom_pere ?? '',
                                'nom_mere'       => $details['nom_mere'] ?? $details['mere_nom'] ?? $item['nom_mere'] ?? $request->nom_mere ?? '',
                                'prenom_mere'    => $details['prenom_mere'] ?? $details['mere_prenom'] ?? $item['prenom_mere'] ?? $request->prenom_mere ?? '',
                                'date_acte'      => now(),
                            ]));
                            break;

                        case 'mariage':
                            $acteModel = Mariage::create(array_merge($commonData, [
                                'nom_epoux'             => $details['nom_epoux'] ?? $details['epoux_nom'] ?? $item['nom_epoux'] ?? $request->nom_epoux ?? '',
                                'prenom_epoux'          => $details['prenom_epoux'] ?? $details['epoux_prenom'] ?? $item['prenom_epoux'] ?? $request->prenom_epoux ?? '',
                                'date_naissance_epoux'  => $details['date_naissance_epoux'] ?? $details['epoux_date_naissance'] ?? $item['date_naissance_epoux'] ?? $request->date_naissance_epoux ?? null,
                                'lieu_naissance_epoux'  => $details['lieu_naissance_epoux'] ?? $details['epoux_lieu_naissance'] ?? $item['lieu_naissance_epoux'] ?? $request->lieu_naissance_epoux ?? '',
                                'nom_epouse'            => $details['nom_epouse'] ?? $details['epouse_nom'] ?? $item['nom_epouse'] ?? $request->nom_epouse ?? '',
                                'prenom_epouse'         => $details['prenom_epouse'] ?? $details['epouse_prenom'] ?? $item['prenom_epouse'] ?? $request->prenom_epouse ?? '',
                                'date_naissance_epouse' => $details['date_naissance_epouse'] ?? $details['epouse_date_naissance'] ?? $item['date_naissance_epouse'] ?? $request->date_naissance_epouse ?? null,
                                'lieu_naissance_epouse' => $details['lieu_naissance_epouse'] ?? $details['epouse_lieu_naissance'] ?? $item['lieu_naissance_epouse'] ?? $request->lieu_naissance_epouse ?? '',
                                'date_mariage'          => $details['date_mariage'] ?? $item['date_mariage'] ?? $request->date_mariage ?? null,
                                'lieu_mariage'          => $details['lieu_mariage'] ?? $item['lieu_mariage'] ?? $request->lieu_mariage ?? '',
                            ]));
                            break;

                        case 'deces':
                            $acteModel = Deces::create(array_merge($commonData, [
                                'nom_defunt'            => $details['nom_defunt'] ?? $details['defunt_nom'] ?? $item['nom_defunt'] ?? $request->nom_defunt ?? $request->personne_nom ?? '',
                                'prenom_defunt'         => $details['prenom_defunt'] ?? $details['defunt_prenom'] ?? $item['prenom_defunt'] ?? $request->prenom_defunt ?? $request->personne_prenom ?? '',
                                'date_naissance_defunt' => $details['date_naissance_defunt'] ?? $details['defunt_date_naissance'] ?? $item['date_naissance_defunt'] ?? $request->date_naissance_defunt ?? null,
                                'date_deces'            => $details['date_deces'] ?? $item['date_deces'] ?? $request->date_deces ?? null,
                                'lieu_deces'            => $details['lieu_deces'] ?? $item['lieu_deces'] ?? $request->lieu_deces ?? '',
                                'cause_deces'           => $details['cause_deces'] ?? $item['cause_deces'] ?? $request->cause_deces ?? null,
                            ]));
                            break;

                        case 'divorces':
                            Log::info('Details pour divorce:', ['details' => $details]);
                            Log::info('CommonData pour divorce:', ['commonData' => $commonData]);
                            // S'assurer que $details est un tableau
                            $details = (array) $details;

                            $acteModel = Divorce::create(array_merge($commonData, [
                                'nom_epoux'     => $details['conjoint_nom'] ?? '',
                                'prenom_epoux'  => $details['conjoint_prenom'] ?? '',
                                'nom_epouse'    => $details['conjointe_nom'] ?? '',
                                'prenom_epouse' => $details['conjointe_prenom'] ?? '',
                                'date_mariage'  => $details['date_mariage'] ?? null,
                                'date_jugement' => $details['date_demande_divorce'] ?? null,
                                'motif'         => $details['motif'] ?? null,
                                'tribunal'      => $details['tribunal'] ?? 'À préciser',      // ✅ Ajout
                                'num_jugement'  => $details['num_jugement'] ?? 'Non renseigné',
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
                            'supplement_id'       => $supplementId, 
                            'acte_type'     => get_class($acteModel),
                            'acte_id'       => $acteModel->getKey(),
                            'langue'        => $langue,
                            'prix_acte'           => $prixActe,
                            'prix_supplement'     => $prixSupplement,
                            'prix_unitaire' => $prixActe + $prixSupplement,
                            'quantite_supplement' => $quantiteSupplement,
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
                
                // ============================================================
                // 5️⃣ CALCUL DE L'ESTIMATION (AJOUTER ICI)
                // Express = 24h, Standard = 72h
                // ============================================================
                $delaiHeures = $request->service === 'express' ? 24 : 72;
                $dateEstimation = now()->addHours($delaiHeures);

                $demande->update([
                    'prix_total'          => $prixTotalGlobal,
                    'nombre_actes'        => $totalNombreActes,
                    'delai_heures'        => $delaiHeures,
                    'date_estimation'     => $dateEstimation,
                    'estimation_envoyee'  => false,
                ]);
                \Log::info('📅 Estimation programmée:', [
                    'reference'       => $demande->reference,
                    'service'         => $request->service,
                    'delai_heures'    => $delaiHeures,
                    'date_estimation' => $dateEstimation->toDateTimeString(),
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
                ->with(['demandeActes.typeActe', 'demandeActes.acte', 'demandeActes.supplement'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'demandes' => $demandes,
                'demandeActes' => $demandes->flatMap->demandeActes->values()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur mesDemandes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la récupération des demandes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function statistiques(Request $request)
    {
        $citoyen = Auth::user();
        if (!$citoyen) {
         return response()->json(['message' => 'Non authentifié'], 401);
        }

        $citoyenId = $citoyen->id_citoyens ?? $citoyen->id;
        $periode = $request->get('periode', '12m');

        // ⚠️ Attention aux accents dans le statut : 'acceptée' vs 'acceptee'
        $statuts = [
            'acceptee'  => ['acceptée', 'acceptee', 'accepté', 'accepte'],
            'refusee'   => ['refusée', 'refusee', 'refusé', 'refuse'],
            'attente'   => ['en_attente', 'en attente'],
        ];

        $query = Demande::where('citoyen_id', $citoyenId);

        $total     = (clone $query)->count();
        $acceptees = (clone $query)->whereIn('statut', $statuts['acceptee'])->count();
        $refusees  = (clone $query)->whereIn('statut', $statuts['refusee'])->count();
        $enAttente = (clone $query)->whereIn('statut', $statuts['attente'])->count();

        // ✅ Par mois (12 derniers mois)
        $parMois = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Demande::where('citoyen_id', $citoyenId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $parMois[] = [
                'label' => $date->translatedFormat('M'),
                'total' => $count,
            ];
        }

        return response()->json([
            'total'      => $total,
            'acceptees'  => $acceptees,
            'refusees'   => $refusees,
            'en_attente' => $enAttente,
            'par_mois'   => $parMois,
        ]);
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

            // ✅ Normaliser le rôle (gérer underscore/tiret/espaces/casse)
            $role = strtolower(str_replace(['-', ' '], '_', $user->role ?? 'citoyen'));

            \Log::info('🔍 show() demandé', [
                'id' => $id,
                'user_id' => $user->id,
                'role_brut' => $user->role,
                'role_normalise' => $role,
            ]);

            // ✅ Admin & Super Admin & Agent → accès TOTAL
            if (in_array($role, ['admin', 'super_admin', 'agent'])) {

                $demande = Demande::with([
                    'citoyen',
                    'traiteur',
                    'demandeActes.typeActe',
                    'demandeActes.supplement',
                    'demandeActes.acte',
                ])->find($id);

                if (!$demande) {
                    \Log::warning('⚠️ Demande introuvable: id=' . $id);
                    return redirect()->back()->with('error', 'Demande introuvable.');
                }

                // ✅ Retour JSON si API
                if (request()->wantsJson()) {
                    return response()->json(['demande' => $demande], 200);
                }

                // ✅ Vue selon le rôle
                $vue = $role === 'super_admin'
                    ? 'super-admin.demandes.show'
                    : 'admin.demandes_show';

                // Si la vue super-admin n'existe pas, on fallback sur admin
                if (!view()->exists($vue)) {
                    $vue = 'admin.demandes_show';
                }

                // Si admin.demandes_show n'existe pas non plus → vue de secours
                if (!view()->exists($vue)) {
                    \Log::warning('⚠️ Aucune vue de détail trouvée, fallback');
                    return response()->json(['demande' => $demande], 200);
                }

                return view($vue, compact('demande'));
            }

            // ✅ Citoyen → uniquement ses demandes
            $userId = $user->id_citoyens ?? $user->id;

            $demande = Demande::where('citoyen_id', $userId)
                ->with([
                    'demandeActes.typeActe',
                    'demandeActes.supplement',
                    'demandeActes.acte',
                ])
                ->find($id);

            if (!$demande) {
                return redirect()->back()->with('error', 'Demande introuvable.');
            }

            if (request()->wantsJson()) {
                return response()->json(['demande' => $demande], 200);
            }

            return view('admin.demandes_show', compact('demande'));

        } catch (\Exception $e) {
            \Log::error('❌ Erreur show(): ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function verifierStatut($reference)
    {
        try {
            $citoyen = Auth::guard('citoyen')->user();
        
            if (!$citoyen) {
                return response()->json(['success' => false], 401);
            }

            $demande = Demande::where('citoyen_id', $citoyen->id_citoyens)
                ->where('reference', $reference)
                ->select('id_demande', 'reference', 'statut', 'date_traitement')
                ->first();

            if (!$demande) {
                return response()->json(['success' => false, 'message' => 'Demande non trouvée'], 404);
            }

            return response()->json([
                'success' => true,
                'statut' => $demande->statut,
                'date_traitement' => $demande->date_traitement,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
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