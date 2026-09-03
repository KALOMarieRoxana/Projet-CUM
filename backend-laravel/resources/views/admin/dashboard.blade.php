@extends('layouts.app')

@section('title', 'Tableau de bord - Demandes')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1">Tableau de bord</h4>
    <p class="text-muted small">Vue d'ensemble et gestion des demandes reçues.</p>
</div>

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
                    <th>Demandeur</th>
                    <th>Sujet</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes ?? [] as $demande)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $demande->user->name }}</div>
                            <small class="text-muted">{{ $demande->user->email }}</small>
                        </td>
                        <td>{{ $demande->sujet }}</td>
                        <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if($demande->statut == 'en_attente')
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">En attente</span>
                            @elseif($demande->statut == 'acceptee')
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Acceptée</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Refusée</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($demande->statut == 'en_attente')
                                <form action="{{ route('admin.demandes.update', $demande->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="statut" value="acceptee">
                                    <button class="btn btn-sm btn-success me-1"><i class="bi bi-check-lg"></i> Accepter</button>
                                </form>
                                <form action="{{ route('admin.demandes.update', $demande->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="statut" value="refusee">
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i> Refuser</button>
                                </form>
                            @else
                                <span class="text-muted small">Traité</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Aucune demande trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection