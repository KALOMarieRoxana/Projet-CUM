@extends('layouts.admin')

@section('title', 'Tableau de bord - Super Admin')

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════════════ */
    /* HERO BANNER — Style Able Pro                                */
    /* ═══════════════════════════════════════════════════════════ */
    .hero-banner {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        padding: 32px 40px;
        margin-bottom: 24px;
        background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #4F46E5 100%);
        box-shadow: 0 10px 30px rgba(37, 99, 235, 0.25);
        min-height: 160px;
        display: flex;
        align-items: center;
    }

    .hero-banner::before {
        content: '';
        position: absolute;
        top: -60px;
        right: 15%;
        width: 220px;
        height: 220px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-banner::after {
        content: '';
        position: absolute;
        bottom: -80px;
        right: -40px;
        width: 280px;
        height: 280px;
        background: rgba(255, 255, 255, 0.06);
        border-radius: 50%;
        pointer-events: none;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 60%;
    }

    .hero-title {
        font-size: 26px;
        font-weight: 800;
        color: #FFFFFF;
        margin: 0 0 8px 0;
        letter-spacing: -0.3px;
    }

    .hero-subtitle {
        font-size: 13.5px;
        color: rgba(255, 255, 255, 0.85);
        margin: 0 0 18px 0;
        line-height: 1.5;
        max-width: 480px;
    }

    .hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 20px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #FFFFFF;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        backdrop-filter: blur(10px);
        transition: all 0.2s;
    }
    .hero-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-1px);
        color: #FFFFFF;
    }

    .hero-decoration {
        position: absolute;
        right: 40px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
        color: rgba(255, 255, 255, 0.15);
        font-size: 140px;
        line-height: 1;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .hero-content { max-width: 100%; }
        .hero-decoration { display: none; }
        .hero-title { font-size: 20px; }
    }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- BANNIÈRE HERO — Style Able Pro                              --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="hero-banner">
    <div class="hero-content">
        <h2 class="hero-title">Tableau de bord Super Admin</h2>
        <p class="hero-subtitle">
            Vue d'ensemble globale de la plateforme et gestion administrative.
        </p>
        <a href="{{ route('super-admin.admins.index') }}" class="hero-btn">
            <i class="bi bi-people-fill"></i> Gérer tous les administrateurs
        </a>
    </div>

    <div class="hero-decoration">
        <i class="bi bi-speedometer2"></i>
    </div>
</div>

{{-- ═══════════ ALERTES ═══════════ --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══════════ 4 CARTES STATS ═══════════ --}}
<div class="row g-3 mb-4">

    {{-- Administrateurs --}}
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e0e7ff; color: #4f46e5;">
                <i class="bi bi-shield-lock-fill fs-4"></i>
            </div>
            <div>
                <span class="text-muted small d-block">Administrateurs</span>
                <h3 class="fw-bold mb-0">{{ $totalAdmins }}</h3>
            </div>
        </div>
    </div>

    {{-- Total --}}
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #dbeafe; color: #2563eb;">
                <i class="bi bi-inbox-fill fs-4"></i>
            </div>
            <div>
                <span class="text-muted small d-block">Total Demandes</span>
                <h3 class="fw-bold mb-0">{{ $totalDemandes }}</h3>
            </div>
        </div>
    </div>

    {{-- En attente --}}
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fef3c7; color: #d97706;">
                <i class="bi bi-clock-fill fs-4"></i>
            </div>
            <div>
                <span class="text-muted small d-block">En attente</span>
                <h3 class="fw-bold mb-0">{{ $demandesEnAttente }}</h3>
            </div>
        </div>
    </div>

    {{-- Acceptées --}}
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #d1fae5; color: #059669;">
                <i class="bi bi-check-circle-fill fs-4"></i>
            </div>
            <div>
                <span class="text-muted small d-block">Acceptées</span>
                <h3 class="fw-bold mb-0">{{ $demandesAcceptees }}</h3>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ TABLEAU DES DEMANDES ═══════════ --}}
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">Liste des demandes</h5>
            <p class="text-muted small mb-0">
                {{ method_exists($demandes, 'total') ? $demandes->total() : $demandes->count() }} demande(s) au total
            </p>
        </div>
        <a href="{{ route('super-admin.demandes') }}" class="btn btn-sm btn-outline-primary">
            Voir tout <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Référence</th>
                    <th>Demandeur</th>
                    <th>Contact</th>
                    <th>Service</th>
                    <th>Prix total</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                    <tr>
                        {{-- Référence --}}
                        <td class="ps-3">
                            <div class="fw-semibold">{{ $demande->reference }}</div>
                            <small class="text-muted">#{{ $demande->id_demande ?? $demande->id }}</small>
                        </td>

                        {{-- Demandeur --}}
                        <td>
                            <div class="fw-semibold">
                                {{ $demande->demandeur_prenom ?? '' }}
                                {{ $demande->demandeur_nom ?? '' }}
                            </div>
                            <small class="text-muted">
                                {{ $demande->personne_prenom ?? '' }} {{ $demande->personne_nom ?? '' }}
                            </small>
                        </td>

                        {{-- Contact --}}
                        <td>
                            <div>{{ $demande->demandeur_contact ?? '—' }}</div>
                        </td>

                        {{-- Service --}}
                        <td>
                            @if(($demande->service ?? 'standard') === 'express')
                                <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill">
                                    ⚡ Express
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-pill">
                                    🛡 Standard
                                </span>
                            @endif
                        </td>

                        {{-- Prix --}}
                        <td>
                            <div class="fw-semibold">
                                {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }}
                            </div>
                            <small class="text-muted">Ar</small>
                        </td>

                        {{-- Statut --}}
                        <td>
                            @if($demande->statut === 'acceptée' || $demande->statut === 'acceptee')
                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill">
                                    <i class="bi bi-check-circle"></i> Acceptée
                                </span>
                            @elseif($demande->statut === 'refusée' || $demande->statut === 'refusee')
                                <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill">
                                    <i class="bi bi-x-circle"></i> Refusée
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill">
                                    <i class="bi bi-hourglass-split"></i> En attente
                                </span>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td>
                            <div>{{ $demande->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $demande->created_at->format('H:i') }}</small>
                        </td>

                        {{-- Actions --}}
                        <td class="text-end pe-3" style="min-width: 100px;">
                            <div class="d-flex flex-column gap-1 align-items-end">

                                <a href="{{ route('super-admin.demandes.show', $demande->id_demande ?? $demande->id) }}"
                                   class="btn btn-sm btn-link text-decoration-none p-0"
                                   style="font-size: 12px; color: #374151;">
                                    Voir
                                </a>

                                @if($demande->statut === 'en_attente')
                                    <button type="button"
                                            class="btn btn-sm btn-link text-decoration-none p-0"
                                            style="font-size: 12px; color: #374151;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAction"
                                            data-id="{{ $demande->id_demande ?? $demande->id }}"
                                            data-reference="{{ $demande->reference }}"
                                            data-action="accepter">
                                        Accepter
                                    </button>

                                    <button type="button"
                                            class="btn btn-sm btn-link text-decoration-none p-0"
                                            style="font-size: 12px; color: #374151;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAction"
                                            data-id="{{ $demande->id_demande ?? $demande->id }}"
                                            data-reference="{{ $demande->reference }}"
                                            data-action="refuser">
                                        Refuser
                                    </button>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Aucune demande pour le moment
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if(method_exists($demandes, 'hasPages') && $demandes->hasPages())
        <div class="mt-3 d-flex justify-content-center">
            {{ $demandes->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

{{-- ═══════════ MODAL ACTION ═══════════ --}}
<div class="modal fade" id="modalAction" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="" id="formAction">
                @csrf
                @method('PUT')
                <input type="hidden" name="statut" id="inputStatut">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitre">Confirmer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p id="modalMessage" class="mb-3"></p>

                    <label for="commentaire_admin" class="form-label small fw-bold">
                        Commentaire (optionnel)
                    </label>
                    <textarea name="commentaire_admin"
                              id="commentaire_admin"
                              class="form-control"
                              rows="4"
                              placeholder="Ex: Documents manquants / Demande validée."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="btnConfirmer">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════ SCRIPT ═══════════ --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalAction = document.getElementById('modalAction');

    modalAction.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const reference = button.getAttribute('data-reference');
        const action = button.getAttribute('data-action');

        document.getElementById('formAction').action = `/super-admin/demandes/${id}`;
        document.getElementById('commentaire_admin').value = '';

        const titre = document.getElementById('modalTitre');
        const message = document.getElementById('modalMessage');
        const inputStatut = document.getElementById('inputStatut');
        const btnConfirmer = document.getElementById('btnConfirmer');

        if (action === 'accepter') {
            titre.innerHTML = '<i class="bi bi-check-circle text-success"></i> Accepter la demande';
            message.innerHTML = `Voulez-vous <strong class="text-success">accepter</strong> la demande <strong>${reference}</strong> ?<br><small class="text-muted">Un PDF sera généré et le citoyen sera notifié.</small>`;
            inputStatut.value = 'acceptée';
            btnConfirmer.className = 'btn btn-success';
            btnConfirmer.innerHTML = '<i class="bi bi-check-circle"></i> Confirmer l\'acceptation';
        } else {
            titre.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Refuser la demande';
            message.innerHTML = `Voulez-vous <strong class="text-danger">refuser</strong> la demande <strong>${reference}</strong> ?<br><small class="text-muted">Le citoyen sera notifié du refus.</small>`;
            inputStatut.value = 'refusée';
            btnConfirmer.className = 'btn btn-danger';
            btnConfirmer.innerHTML = '<i class="bi bi-x-circle"></i> Confirmer le refus';
        }
    });
});
</script>
@endpush

@endsection