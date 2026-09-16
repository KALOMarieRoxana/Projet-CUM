@extends('layouts.admin')

@section('title', 'Tableau de bord - Demandes')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1">Tableau de bord</h4>
        <p class="text-muted small mb-0">Vue d'ensemble et gestion des demandes reçues.</p>
    </div>
    <!-- Bouton d'ouverture du modal de gestion des tarifs par service -->
    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTarifsServices">
        <i class="bi bi-gear-fill me-1"></i> Modifier le prix des services
    </button>
</div>

<!-- Messages de confirmation et d'erreur -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- 4 Cases de Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-inbox-fill"></i>
            </div>
            <div>
                <span class="text-muted small d-block">Toutes les demandes</span>
                <h3 class="fw-bold mb-0">{{ $totalDemandes ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-clock-fill"></i>
            </div>
            <div>
                <span class="text-muted small d-block">En attente</span>
                <h3 class="fw-bold mb-0">{{ $demandesEnAttente ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <span class="text-muted small d-block">Acceptées</span>
                <h3 class="fw-bold mb-0">{{ $demandesAcceptees ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div>
                <span class="text-muted small d-block">Refusées</span>
                <h3 class="fw-bold mb-0">{{ $demandesRefusees ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Liste des demandes avec filtre -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Liste des demandes</h5>
        <div class="btn-group btn-group-sm" role="group">
            <a href="?statut=tous" class="btn btn-outline-secondary {{ request('statut', 'tous') == 'tous' ? 'active' : '' }}">Tous</a>
            <a href="?statut=en_attente" class="btn btn-outline-warning {{ request('statut') == 'en_attente' ? 'active' : '' }}">En attente</a>
            <a href="?statut=acceptee" class="btn btn-outline-success {{ request('statut') == 'acceptee' ? 'active' : '' }}">Acceptées</a>
            <a href="?statut=refusee" class="btn btn-outline-danger {{ request('statut') == 'refusee' ? 'active' : '' }}">Refusées</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Référence</th>
                    <th>Demandeur</th>
                    <th>Personne Concernée</th>
                    <th>Service</th>
                    <th>Quantité / Prix</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes ?? [] as $demande)
                    <tr>
                        <!-- Référence et Date -->
                        <td>
                            <span class="fw-bold text-primary">#{{ $demande->reference ?? $demande->getKey() }}</span><br>
                            <small class="text-muted">{{ $demande->created_at?->format('d/m/Y H:i') }}</small>
                        </td>

                        <!-- Demandeur -->
                        <td>
                            <div class="fw-semibold">
                                {{ $demande->citoyen?->nom ?? $demande->demandeur_nom ?? 'N/A' }} 
                                {{ $demande->citoyen?->prenom ?? $demande->demandeur_prenom ?? '' }}
                            </div>
                            <small class="text-muted d-block">
                                <i class="bi bi-envelope"></i> {{ $demande->citoyen?->email ?? $demande->demandeur_contact ?? 'Pas de contact' }}
                            </small>
                            <small class="text-muted d-block">
                                Lien : {{ $demande->demandeur_relation ?? 'N/A' }}
                            </small>
                        </td>

                        <!-- Personne concernée -->
                        <td>
                            <div class="fw-semibold">
                                {{ $demande->personne_nom ?? 'N/A' }} {{ $demande->personne_prenom ?? '' }}
                            </div>
                            <small class="text-muted d-block">
                                Né(e) le : {{ $demande->personne_date_naissance ? \Carbon\Carbon::parse($demande->personne_date_naissance)->format('d/m/Y') : 'N/A' }}
                            </small>
                            <small class="text-muted d-block">
                                Lieu : {{ $demande->personne_lieu_naissance ?? 'N/A' }}
                            </small>
                        </td>

                        <!-- Service / Acte -->
                        <td>
                            <span class="badge bg-info text-dark">
                                {{ ucfirst(str_replace('_', ' ', $demande->service ?? 'N/A')) }}
                            </span>
                        </td>

                        <!-- Quantité & Prix -->
                        <td>
                            <div>{{ $demande->nombre_actes ?? 1 }} acte(s)</div>
                            <small class="fw-bold text-success">
                                {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar
                            </small>
                        </td>

                        <!-- Statut -->
                        <td>
                            @if($demande->statut == 'en_attente')
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">En attente</span>
                            @elseif($demande->statut == 'acceptée' || $demande->statut == 'acceptee')
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Acceptée</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Refusée</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#modalDemande{{ $demande->getKey() }}">
                                <i class="bi bi-eye"></i> Voir
                            </button>

                            @if($demande->statut == 'en_attente')
                                <form action="{{ route('admin.demandes.update', $demande->getKey()) }}" method="POST" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="statut" value="acceptée">
                                    <button class="btn btn-sm btn-success me-1" onclick="return confirm('Accepter cette demande ?')">
                                        <i class="bi bi-check-lg"></i> Accepter
                                    </button>
                                </form>
                                <form action="{{ route('admin.demandes.update', $demande->getKey()) }}" method="POST" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="statut" value="refusée">
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Refuser cette demande ?')">
                                        <i class="bi bi-x-lg"></i> Refuser
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">Traité</span>
                            @endif
                        </td>
                    </tr>

                    <!-- MODAL DE DÉTAILS COMPLETS DE LA DEMANDE -->
                    <div class="modal fade" id="modalDemande{{ $demande->getKey() }}" tabindex="-1" aria-labelledby="modalLabel{{ $demande->getKey() }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="modalLabel{{ $demande->getKey() }}">
                                        Détails de la demande #{{ $demande->reference ?? $demande->getKey() }}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                </div>
                                <div class="modal-body text-start">
                                    <div class="row g-3">
                                        <!-- Section Demandeur -->
                                        <div class="col-md-6">
                                            <div class="card h-100 border-light bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary border-bottom pb-2">
                                                        <i class="bi bi-person"></i> Informations du Demandeur
                                                    </h6>
                                                    <p class="mb-1"><strong>Nom & Prénom :</strong> {{ $demande->citoyen?->nom ?? $demande->demandeur_nom ?? 'N/A' }} {{ $demande->citoyen?->prenom ?? $demande->demandeur_prenom ?? '' }}</p>
                                                    <p class="mb-1"><strong>Contact / Email :</strong> {{ $demande->citoyen?->email ?? $demande->demandeur_contact ?? 'Non renseigné' }}</p>
                                                    <p class="mb-1"><strong>Adresse :</strong> {{ $demande->demandeur_adresse ?? 'Non renseignée' }}</p>
                                                    <p class="mb-0"><strong>Lien de parenté :</strong> {{ $demande->demandeur_relation ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section Personne Concernée -->
                                        <div class="col-md-6">
                                            <div class="card h-100 border-light bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary border-bottom pb-2">
                                                        <i class="bi bi-file-earmark-person"></i> Personne Concernée
                                                    </h6>
                                                    <p class="mb-1"><strong>Nom & Prénom :</strong> {{ $demande->personne_nom ?? 'N/A' }} {{ $demande->personne_prenom ?? '' }}</p>
                                                    <p class="mb-1"><strong>Date de naissance :</strong> {{ $demande->personne_date_naissance ? \Carbon\Carbon::parse($demande->personne_date_naissance)->format('d/m/Y') : 'N/A' }}</p>
                                                    <p class="mb-0"><strong>Lieu de naissance :</strong> {{ $demande->personne_lieu_naissance ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section Détails du Service -->
                                        <div class="col-md-12">
                                            <div class="card border-light bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary border-bottom pb-2">
                                                        <i class="bi bi-info-circle"></i> Détails de l'Acte Administratif
                                                    </h6>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <p class="mb-1"><strong>Type de service :</strong> {{ ucfirst(str_replace('_', ' ', $demande->service ?? 'N/A')) }}</p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p class="mb-1"><strong>Nombre d'exemplaires :</strong> {{ $demande->nombre_actes ?? 1 }}</p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p class="mb-1"><strong>Prix Total :</strong> {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar</p>
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <p class="mb-1"><strong>Date de création :</strong> {{ $demande->created_at?->format('d/m/Y à H:i') }}</p>
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <p class="mb-1"><strong>Statut actuel :</strong> 
                                                                <span class="badge bg-secondary">{{ $demande->statut }}</span>
                                                            </p>
                                                        </div>
                                                        @if($demande->commentaire_admin)
                                                            <div class="col-md-12 mt-2">
                                                                <p class="mb-0 text-muted"><strong>Remarque Admin :</strong> {{ $demande->commentaire_admin }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Aucune demande trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL GESTION DES TARIFS DE SERVICE -->
<div class="modal fade" id="modalTarifsServices" tabindex="-1" aria-labelledby="modalTarifsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.services.update-prices') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTarifsLabel">
                        <i class="bi bi-tag-fill me-1"></i> Modifier le prix des services
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Ajustez le tarif unitaire en Ariary (Ar) pour chaque type d'acte d'état civil :</p>
                    
                    @php
                        $servicesTarifs = $services ?? [
                            'acte_naissance' => 2000,
                            'acte_mariage'   => 3000,
                            'acte_deces'     => 2000,
                            'certificat_residence' => 1500,
                            'autre'          => 2000,
                        ];
                    @endphp

                    @foreach($servicesTarifs as $key => $prix)
                        <div class="mb-3">
                            <label for="prix_{{ $key }}" class="form-label fw-semibold text-capitalize">
                                {{ str_replace('_', ' ', $key) }}
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       step="100" 
                                       min="0" 
                                       name="tarifs[{{ $key }}]" 
                                       id="prix_{{ $key }}" 
                                       class="form-class form-control" 
                                       value="{{ is_object($prix) ? $prix->prix_unitaire : $prix }}" 
                                       required>
                                <span class="input-group-text">Ar</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Enregistrer les tarifs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection