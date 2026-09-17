@extends('layouts.admin')

@section('title', 'Types d\'actes')

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════════════ */
    /* HERO BANNER — Vert dégradé                                  */
    /* ═══════════════════════════════════════════════════════════ */
    .hero-banner {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        padding: 32px 40px;
        margin-bottom: 24px;
        background: linear-gradient(135deg, #065F46 0%, #059669 40%, #10B981 75%, #34D399 100%);
        box-shadow: 0 10px 30px rgba(5, 150, 105, 0.25);
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
        background: rgba(255, 255, 255, 0.12);
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
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        pointer-events: none;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 65%;
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
        color: rgba(255, 255, 255, 0.9);
        margin: 0 0 18px 0;
        line-height: 1.5;
        max-width: 520px;
    }

    .hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 20px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.35);
        color: #FFFFFF;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        backdrop-filter: blur(10px);
        transition: all 0.2s;
        cursor: pointer;
    }
    .hero-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
        color: #FFFFFF;
    }

    .hero-decoration {
        position: absolute;
        right: 40px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
        color: rgba(255, 255, 255, 0.2);
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
{{-- BANNIÈRE HERO — VERT DÉGRADÉ                                --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="hero-banner">
    <div class="hero-content">
        <h2 class="hero-title">Types d'actes et tarifs</h2>
        <p class="hero-subtitle">
            Gérez tous les types d'actes d'état civil, leurs tarifs (Standard / Express)
            en Malgache et en Français, ainsi que les suppléments associés.
        </p>
        <a href="{{ route('super-admin.types-actes.create') }}" class="hero-btn">
            <i class="bi bi-plus-lg"></i> Nouveau type d'acte
        </a>
    </div>

    <div class="hero-decoration">
        <i class="bi bi-file-earmark-text-fill"></i>
    </div>
</div>

{{-- ═══════════ ALERTES ═══════════ --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══════════ 3 CARTES STYLE ABLE PRO ═══════════ --}}
<div class="row g-3 mb-4">

    <div class="col-md-4">
        @include('partials.stat-card-chart', [
            'label'       => 'Types d\'actes',
            'valeur'      => $stats['total'],
            'pourcentage' => 0,
            'couleur'     => '#2563EB',
            'bgCouleur'   => '#DBEAFE',
            'icon'        => 'bi-file-earmark-text-fill',
            'points'      => [5, 5, 5, 5, 5, 5, 5, 5, 5, 5],
        ])
    </div>

    <div class="col-md-4">
        @include('partials.stat-card-chart', [
            'label'       => 'Types actifs',
            'valeur'      => $stats['actifs'],
            'pourcentage' => 0,
            'couleur'     => '#059669',
            'bgCouleur'   => '#D1FAE5',
            'icon'        => 'bi-check-circle-fill',
            'points'      => [5, 5, 5, 5, 5, 5, 5, 5, 5, 5],
        ])
    </div>

    <div class="col-md-4">
        @include('partials.stat-card-chart', [
            'label'       => 'Suppléments',
            'valeur'      => $stats['supplements'],
            'pourcentage' => 0,
            'couleur'     => '#F59E0B',
            'bgCouleur'   => '#FEF3C7',
            'icon'        => 'bi-file-plus-fill',
            'points'      => [5, 5, 5, 5, 5, 5, 5, 5, 5, 5],
        ])
    </div>
</div>

{{-- ═══════════ TABLEAU ═══════════ --}}
<div class="content-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Liste des types d'actes</h5>
            <small class="text-muted">{{ $typesActes->count() }} type(s) enregistré(s)</small>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Nom</th>
                    <th>Code</th>
                    <th>Tarifs (Ar)</th>
                    <th>Suppléments</th>
                    <th>Statut</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($typesActes as $type)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold">{{ $type->nom }}</div>
                            <small class="text-muted">Sigle : {{ $type->sigle ?? '—' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $type->type_acte }}</span>
                        </td>
                        <td>
                            <div style="font-size: 11.5px;">
                                <div><strong>MG</strong> Std : {{ number_format($type->montantStandardMG, 0, ',', ' ') }} Ar</div>
                                <div><strong>MG</strong> Exp : {{ number_format($type->montantExpressMG, 0, ',', ' ') }} Ar</div>
                                <div><strong>FR</strong> Std : {{ number_format($type->montantStandardFR, 0, ',', ' ') }} Ar</div>
                                <div><strong>FR</strong> Exp : {{ number_format($type->montantExpressFR, 0, ',', ' ') }} Ar</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $type->supplements_count }} suppl.</span>
                        </td>
                        <td>
                            @if($type->actif)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('super-admin.types-actes.edit', $type->id) }}"
                               class="btn btn-sm btn-outline-primary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('super-admin.types-actes.toggle-actif', $type->id) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning"
                                        title="{{ $type->actif ? 'Désactiver' : 'Activer' }}">
                                    <i class="bi bi-{{ $type->actif ? 'pause' : 'play' }}-fill"></i>
                                </button>
                            </form>

                            <form action="{{ route('super-admin.types-actes.destroy', $type->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer ce type d\'acte ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
                            Aucun type d'acte enregistré
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection