@extends('layouts.app')

@section('title', 'Tableau de bord - Super Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tableau de bord Super Admin</h4>
        <p class="text-muted small">Vue d'ensemble globale de la plateforme et gestion administrative.</p>
    </div>
    <a href="{{ route('super-admin.admins.index') }}" class="btn btn-primary">
        <i class="bi bi-people-fill me-1"></i> Gérer tous les administrateurs
    </a>
</div>

<!-- 4 Cartes d'indicateurs clés -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-indigo bg-opacity-10 text-primary" style="background-color: #e0e7ff; color: #4f46e5;">
                <i class="bi bi-shield-lock-fill fs-4"></i>
            </div>
            <div>
                <span class="text-muted small d-block">Administrateurs</span>
                <h3 class="fw-bold mb-0">{{ $totalAdmins }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-inbox-fill fs-4"></i>
            </div>
            <div>
                <span class="text-muted small d-block">Total Demandes</span>
                <h3 class="fw-bold mb-0">{{ $totalDemandes }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-clock-fill fs-4"></i>
            </div>
            <div>
                <span class="text-muted small d-block">En attente</span>
                <h3 class="fw-bold mb-0">{{ $demandesEnAttente }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle-fill fs-4"></i>
            </div>
            <div>
                <span class="text-muted small d-block">Acceptées</span>
                <h3 class="fw-bold mb-0">{{ $demandesAcceptees }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Section 1 : Liste rapide des Administrateurs -->
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Administrateurs récents</h5>
                <a href="{{ route('super-admin.admins.index') }}" class="btn btn-sm btn-link text-decoration-none">Voir tout</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td class="fw-semibold">{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->contact ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">Aucun administrateur enregistré.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 2 : Aperçu des dernières demandes -->
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Dernières demandes reçues</h5>
                <a href="{{ route('super-admin.demandes') }}" class="btn btn-sm btn-link text-decoration-none">Voir tout</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Demandeur</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demandes as $demande)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $demande->user->name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    @if($demande->statut == 'en_attente')
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill">En attente</span>
                                    @elseif($demande->statut == 'acceptee')
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill">Acceptée</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill">Refusée</span>
                                    @endif
                                </td>
                                <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">Aucune demande récente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection