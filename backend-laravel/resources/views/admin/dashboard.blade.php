@extends('layouts.admin')

@section('title', 'Tableau de bord - Demandes')

@push('styles')
<style>
    /* HERO BANNER */
    .hero-banner {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        padding: 32px 40px;
        margin-bottom: 24px;
        background: linear-gradient(135deg, #B45309 0%, #D97706 40%, #F59E0B 75%, #FBBF24 100%);
        box-shadow: 0 10px 30px rgba(217, 119, 6, 0.25);
        min-height: 160px;
        display: flex;
        align-items: center;
    }
    .hero-banner::before {
        content: '';
        position: absolute;
        top: -60px; right: 15%;
        width: 220px; height: 220px;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-banner::after {
        content: '';
        position: absolute;
        bottom: -80px; right: -40px;
        width: 280px; height: 280px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-content { position: relative; z-index: 2; max-width: 60%; }
    .hero-title {
        font-size: 26px; font-weight: 800;
        color: #FFFFFF; margin: 0 0 8px 0;
        letter-spacing: -0.3px;
    }
    .hero-subtitle {
        font-size: 13.5px;
        color: rgba(255, 255, 255, 0.9);
        margin: 0 0 18px 0; line-height: 1.5;
        max-width: 480px;
    }
    .hero-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 20px; border-radius: 8px;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.35);
        color: #FFFFFF; font-size: 13px; font-weight: 600;
        text-decoration: none; backdrop-filter: blur(10px);
        transition: all 0.2s; cursor: pointer;
    }
    .hero-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px); color: #FFFFFF;
    }
    .hero-decoration {
        position: absolute; right: 40px; top: 50%;
        transform: translateY(-50%); z-index: 1;
        color: rgba(255, 255, 255, 0.2);
        font-size: 140px; line-height: 1;
        pointer-events: none;
    }
    @media (max-width: 768px) {
        .hero-content { max-width: 100%; }
        .hero-decoration { display: none; }
        .hero-title { font-size: 20px; }
    }

    /* ✅ BLOC MOTIF DE REFUS (dans le tableau) */
    .motif-refus {
        display: inline-flex;
        align-items: flex-start;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 8px;
        background: #FEE2E2;
        color: #991B1B;
        font-size: 11px;
        border: 1px solid #FCA5A5;
        max-width: 220px;
        margin-top: 4px;
    }
    .motif-refus i {
        font-size: 12px;
        margin-top: 2px;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')

{{-- BANNIÈRE HERO --}}
<div class="hero-banner">
    <div class="hero-content">
        <h2 class="hero-title">Bienvenue dans votre espace Admin</h2>
        <p class="hero-subtitle">
            Gérez efficacement les demandes d'actes d'état civil, suivez les paiements
            et administrez les services en toute simplicité.
        </p>
        <button type="button"
                class="hero-btn"
                data-bs-toggle="modal"
                data-bs-target="#modalTarifsServices">
            <i class="bi bi-gear-fill"></i> Modifier le prix des services
        </button>
    </div>
    <div class="hero-decoration">
        <i class="bi bi-bar-chart-line-fill"></i>
    </div>
</div>

{{-- ALERTES --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- 4 CARTES STATS --}}
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

{{-- LISTE DES DEMANDES --}}
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-list-ul text-primary me-2"></i>Liste des demandes
        </h5>
        <div class="btn-group btn-group-sm" role="group">
            <a href="?statut=tous" class="btn btn-outline-secondary {{ request('statut', 'tous') == 'tous' ? 'active' : '' }}">
                <i class="bi bi-grid me-1"></i>Tous
            </a>
            <a href="?statut=en_attente" class="btn btn-outline-warning {{ request('statut') == 'en_attente' ? 'active' : '' }}">
                <i class="bi bi-clock me-1"></i>En attente
            </a>
            <a href="?statut=acceptee" class="btn btn-outline-success {{ request('statut') == 'acceptee' ? 'active' : '' }}">
                <i class="bi bi-check-circle me-1"></i>Acceptées
            </a>
            <a href="?statut=refusee" class="btn btn-outline-danger {{ request('statut') == 'refusee' ? 'active' : '' }}">
                <i class="bi bi-x-circle me-1"></i>Refusées
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th><i class="bi bi-hash me-1"></i>Référence</th>
                    <th><i class="bi bi-person me-1"></i>Demandeur</th>
                    <th><i class="bi bi-person-vcard me-1"></i>Personne Concernée</th>
                    <th><i class="bi bi-lightning-charge me-1"></i>Service</th>
                    <th><i class="bi bi-cash-coin me-1"></i>Quantité / Prix</th>
                    <th><i class="bi bi-info-circle me-1"></i>Statut</th>
                    <th class="text-end"><i class="bi bi-gear me-1"></i>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes ?? [] as $demande)
                    @php
                        $estRefusee = in_array($demande->statut, ['refusée', 'refusee']);
                    @endphp
                    <tr>
                        {{-- Référence --}}
                        <td>
                            <span class="fw-bold text-primary">
                                <i class="bi bi-hash me-1"></i>{{ $demande->reference ?? $demande->getKey() }}
                            </span><br>
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>{{ $demande->created_at?->format('d/m/Y H:i') }}
                            </small>
                        </td>

                        {{-- Demandeur --}}
                        <td>
                            <div class="fw-semibold">
                                <i class="bi bi-person-circle text-muted me-1"></i>
                                {{ $demande->citoyen?->nom ?? $demande->demandeur_nom ?? 'N/A' }}
                                {{ $demande->citoyen?->prenom ?? $demande->demandeur_prenom ?? '' }}
                            </div>
                            <small class="text-muted d-block">
                                <i class="bi bi-envelope me-1"></i>{{ $demande->citoyen?->email ?? $demande->demandeur_contact ?? 'Pas de contact' }}
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-people me-1"></i>Lien : {{ $demande->demandeur_relation ?? 'N/A' }}
                            </small>
                        </td>

                        {{-- Personne concernée --}}
                        <td>
                            <div class="fw-semibold">
                                <i class="bi bi-person me-1 text-muted"></i>
                                {{ $demande->personne_nom ?? 'N/A' }} {{ $demande->personne_prenom ?? '' }}
                            </div>
                            <small class="text-muted d-block">
                                <i class="bi bi-calendar-event me-1"></i>
                                Né(e) le : {{ $demande->personne_date_naissance ? \Carbon\Carbon::parse($demande->personne_date_naissance)->format('d/m/Y') : 'N/A' }}
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-geo-alt me-1"></i>
                                Lieu : {{ $demande->personne_lieu_naissance ?? 'N/A' }}
                            </small>
                        </td>

                        {{-- Service --}}
                        <td>
                            <span class="badge bg-info text-dark">
                                <i class="bi bi-lightning-charge-fill me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $demande->service ?? 'N/A')) }}
                            </span>
                        </td>

                        {{-- Quantité & Prix --}}
                        <td>
                            <div>
                                <i class="bi bi-files me-1 text-muted"></i>
                                {{ $demande->nombre_actes ?? 1 }} acte(s)
                            </div>
                            <small class="fw-bold text-success">
                                <i class="bi bi-cash-coin me-1"></i>
                                {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar
                            </small>
                        </td>

                        {{-- Statut + Motif refus --}}
                        <td>
                            @if($demande->statut == 'en_attente')
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">
                                    <i class="bi bi-clock-fill me-1"></i>En attente
                                </span>
                            @elseif($demande->statut == 'acceptée' || $demande->statut == 'acceptee')
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i>Acceptée
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                    <i class="bi bi-x-circle-fill me-1"></i>Refusée
                                </span>
                            @endif

                            @if($estRefusee && $demande->commentaire_admin)
                                <div class="motif-refus">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>
                                        <strong>Motif :</strong>
                                        {{ \Illuminate\Support\Str::limit($demande->commentaire_admin, 80) }}
                                    </span>
                                </div>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="text-end">
                            <button type="button"
                                    class="btn btn-sm btn-outline-info me-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDemande{{ $demande->getKey() }}">
                                <i class="bi bi-eye"></i> Voir
                            </button>

                            @if($demande->statut == 'en_attente')
                                {{-- ✅ BOUTON ACCEPTER → ouvre le modal --}}
                                <button type="button"
                                        class="btn btn-sm btn-success me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAction"
                                        data-id="{{ $demande->getKey() }}"
                                        data-reference="{{ $demande->reference }}"
                                        data-action="accepter">
                                    <i class="bi bi-check-lg"></i> Accepter
                                </button>

                                {{-- ✅ BOUTON REFUSER → ouvre le modal (avec motif obligatoire) --}}
                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAction"
                                        data-id="{{ $demande->getKey() }}"
                                        data-reference="{{ $demande->reference }}"
                                        data-action="refuser">
                                    <i class="bi bi-x-lg"></i> Refuser
                                </button>
                            @else
                                <span class="text-muted small">
                                    <i class="bi bi-check2-all me-1"></i>Traité
                                </span>
                            @endif
                        </td>
                    </tr>

                    {{-- MODAL DE DÉTAILS --}}
                    <div class="modal fade" id="modalDemande{{ $demande->getKey() }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">
                                        <i class="bi bi-pin-angle-fill me-2"></i>
                                        Détails de la demande #{{ $demande->reference ?? $demande->getKey() }}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-start">
                                    <div class="row g-3">
                                        {{-- Demandeur --}}
                                        <div class="col-md-6">
                                            <div class="card h-100 border-light bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary border-bottom pb-2">
                                                        <i class="bi bi-person-badge me-1"></i>Informations du Demandeur
                                                    </h6>
                                                    <p class="mb-1">
                                                        <i class="bi bi-person me-1 text-muted"></i>
                                                        <strong>Nom & Prénom :</strong>
                                                        {{ $demande->citoyen?->nom ?? $demande->demandeur_nom ?? 'N/A' }}
                                                        {{ $demande->citoyen?->prenom ?? $demande->demandeur_prenom ?? '' }}
                                                    </p>
                                                    <p class="mb-1">
                                                        <i class="bi bi-envelope me-1 text-muted"></i>
                                                        <strong>Contact / Email :</strong>
                                                        {{ $demande->citoyen?->email ?? $demande->demandeur_contact ?? 'Non renseigné' }}
                                                    </p>
                                                    <p class="mb-1">
                                                        <i class="bi bi-geo-alt me-1 text-muted"></i>
                                                        <strong>Adresse :</strong>
                                                        {{ $demande->demandeur_adresse ?? 'Non renseignée' }}
                                                    </p>
                                                    <p class="mb-0">
                                                        <i class="bi bi-people me-1 text-muted"></i>
                                                        <strong>Lien de parenté :</strong>
                                                        {{ $demande->demandeur_relation ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Personne concernée --}}
                                        <div class="col-md-6">
                                            <div class="card h-100 border-light bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary border-bottom pb-2">
                                                        <i class="bi bi-person-vcard me-1"></i>Personne Concernée
                                                    </h6>
                                                    <p class="mb-1">
                                                        <i class="bi bi-person me-1 text-muted"></i>
                                                        <strong>Nom & Prénom :</strong>
                                                        {{ $demande->personne_nom ?? 'N/A' }} {{ $demande->personne_prenom ?? '' }}
                                                    </p>
                                                    <p class="mb-1">
                                                        <i class="bi bi-calendar-event me-1 text-muted"></i>
                                                        <strong>Date de naissance :</strong>
                                                        {{ $demande->personne_date_naissance ? \Carbon\Carbon::parse($demande->personne_date_naissance)->format('d/m/Y') : 'N/A' }}
                                                    </p>
                                                    <p class="mb-0">
                                                        <i class="bi bi-geo me-1 text-muted"></i>
                                                        <strong>Lieu de naissance :</strong>
                                                        {{ $demande->personne_lieu_naissance ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Détails --}}
                                        <div class="col-md-12">
                                            <div class="card border-light bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary border-bottom pb-2">
                                                        <i class="bi bi-info-circle me-1"></i>Détails de l'Acte Administratif
                                                    </h6>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <p class="mb-1">
                                                                <i class="bi bi-lightning-charge me-1 text-muted"></i>
                                                                <strong>Type de service :</strong>
                                                                {{ ucfirst(str_replace('_', ' ', $demande->service ?? 'N/A')) }}
                                                            </p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p class="mb-1">
                                                                <i class="bi bi-files me-1 text-muted"></i>
                                                                <strong>Nombre d'exemplaires :</strong>
                                                                {{ $demande->nombre_actes ?? 1 }}
                                                            </p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p class="mb-1">
                                                                <i class="bi bi-cash-coin me-1 text-success"></i>
                                                                <strong>Prix Total :</strong>
                                                                <span class="text-success fw-bold">
                                                                    {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <p class="mb-1">
                                                                <i class="bi bi-calendar3 me-1 text-muted"></i>
                                                                <strong>Date de création :</strong>
                                                                {{ $demande->created_at?->format('d/m/Y à H:i') }}
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <p class="mb-1">
                                                                <i class="bi bi-info-circle me-1 text-muted"></i>
                                                                <strong>Statut actuel :</strong>
                                                                @if($demande->statut == 'en_attente')
                                                                    <span class="badge bg-warning text-dark">
                                                                        <i class="bi bi-clock-fill me-1"></i>En attente
                                                                    </span>
                                                                @elseif(in_array($demande->statut, ['acceptée', 'acceptee']))
                                                                    <span class="badge bg-success">
                                                                        <i class="bi bi-check-circle-fill me-1"></i>Acceptée
                                                                    </span>
                                                                @elseif(in_array($demande->statut, ['refusée', 'refusee']))
                                                                    <span class="badge bg-danger">
                                                                        <i class="bi bi-x-circle-fill me-1"></i>Refusée
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-secondary">{{ $demande->statut }}</span>
                                                                @endif
                                                            </p>
                                                        </div>

                                                        {{-- ✅ MOTIF DU REFUS (dans le modal) --}}
                                                        @if($estRefusee && $demande->commentaire_admin)
                                                            <div class="col-md-12 mt-3">
                                                                <div class="alert alert-danger d-flex align-items-start gap-2 mb-0">
                                                                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                                                                    <div>
                                                                        <strong>Motif du refus :</strong>
                                                                        <div class="mt-1">{{ $demande->commentaire_admin }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        {{-- Remarque Admin (autre que refus) --}}
                                                        @if(!$estRefusee && $demande->commentaire_admin)
                                                            <div class="col-md-12 mt-2">
                                                                <p class="mb-0 text-muted">
                                                                    <i class="bi bi-chat-left-text me-1"></i>
                                                                    <strong>Remarque Admin :</strong>
                                                                    {{ $demande->commentaire_admin }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="bi bi-x-lg me-1"></i>Fermer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Aucune demande trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- ✅ MODAL ACTION — Accepter / Refuser avec motif obligatoire  --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalAction" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="" id="formAction">
                @csrf
                @method('PUT')
                <input type="hidden" name="statut" id="inputStatut">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitre">
                        <i class="bi bi-question-circle me-1"></i>Confirmer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p id="modalMessage" class="mb-3"></p>

                    <label for="commentaire_admin" class="form-label small fw-bold" id="labelCommentaire">
                        Commentaire (optionnel)
                    </label>
                    <textarea name="commentaire_admin"
                              id="commentaire_admin"
                              class="form-control"
                              rows="4"
                              placeholder="Ex : Documents manquants / Demande validée."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnConfirmer">
                        <i class="bi bi-check-lg me-1"></i>Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL GESTION DES TARIFS DE SERVICE --}}
<div class="modal fade" id="modalTarifsServices" tabindex="-1" aria-labelledby="modalTarifsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.services.update-prices') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTarifsLabel">
                        <i class="bi bi-tag-fill me-1"></i>Modifier le prix des services
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Ajustez le tarif unitaire en Ariary (Ar) pour chaque type d'acte d'état civil :
                    </p>

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
                                <i class="bi bi-tag me-1 text-muted"></i>
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
                                <span class="input-group-text">
                                    <i class="bi bi-currency-exchange me-1"></i>Ar
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Enregistrer les tarifs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ SCRIPT DU MODAL ACTION --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalAction = document.getElementById('modalAction');
    const formAction = document.getElementById('formAction');
    const commentaire = document.getElementById('commentaire_admin');
    const label = document.getElementById('labelCommentaire');

    if (!modalAction) return;

    // ═══════════════════════════════════════════════════════
    // À L'OUVERTURE DE LA MODALE
    // ═══════════════════════════════════════════════════════
    modalAction.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const reference = button.getAttribute('data-reference');
        const action = button.getAttribute('data-action');

        // URL de l'action
        formAction.action = `/admin/demandes/${id}`;
        commentaire.value = '';
        commentaire.placeholder = '';

        const titre = document.getElementById('modalTitre');
        const message = document.getElementById('modalMessage');
        const inputStatut = document.getElementById('inputStatut');
        const btnConfirmer = document.getElementById('btnConfirmer');

        if (action === 'accepter') {
            // ✅ ACCEPTATION
            titre.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i>Accepter la demande';
            message.innerHTML = `Voulez-vous <strong class="text-success">accepter</strong> la demande <strong>${reference}</strong> ?<br><small class="text-muted">Un PDF sera généré et le citoyen sera notifié.</small>`;
            inputStatut.value = 'acceptée';
            btnConfirmer.className = 'btn btn-success';
            btnConfirmer.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirmer l\'acceptation';

            label.className = 'form-label small fw-bold';
            label.textContent = 'Commentaire (optionnel)';
            commentaire.placeholder = 'Ex : Demande validée.';

        } else {
            // ✅ REFUS
            titre.innerHTML = '<i class="bi bi-x-circle text-danger me-1"></i>Refuser la demande';
            message.innerHTML = `Voulez-vous <strong class="text-danger">refuser</strong> la demande <strong>${reference}</strong> ?<br><small class="text-muted">Vous devez écrire un motif. Le citoyen sera notifié.</small>`;
            inputStatut.value = 'refusée';
            btnConfirmer.className = 'btn btn-danger';
            btnConfirmer.innerHTML = '<i class="bi bi-x-circle me-1"></i>Confirmer le refus';

            label.className = 'form-label small fw-bold text-danger';
            label.innerHTML = 'Motif du refus <span class="text-danger">*</span>';
            commentaire.placeholder = "Ex : Le numéro d'acte est introuvable dans nos registres.\nEx : La relation déclarée n'est pas compatible.\nEx : La personne concernée n'est pas née à Mahajanga.";
        }
    });

    // ═══════════════════════════════════════════════════════
    // VALIDATION : motif obligatoire pour un refus
    // ═══════════════════════════════════════════════════════
    formAction.addEventListener('submit', function (e) {
        const statut = document.getElementById('inputStatut').value;

        if (statut === 'refusée') {
            const motif = commentaire.value.trim();
            if (!motif) {
                e.preventDefault();
                alert('⚠️ Veuillez écrire le motif du refus.');
                commentaire.focus();
                return false;
            }
        }
    });
});
</script>
@endpush

@endsection