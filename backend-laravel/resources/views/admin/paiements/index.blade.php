@extends('layouts.admin')

@section('title', 'Paiement des demandes')

@section('content')

{{-- ═══════════ HEADER ═══════════ --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Paiement des demandes</h4>
        <small class="text-muted">Encaissement en espèces uniquement</small>
    </div>
    <nav>
        <span class="text-muted">Maison</span> &gt; <span>Paiement</span>
    </nav>
</div>

{{-- ═══════════ ALERTES ═══════════ --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══════════ STATS ═══════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef2ff;color:#4f46e5;">
                <i class="bi bi-file-earmark-text fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted small mb-0">Demandes acceptées</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;color:#059669;">
                <i class="bi bi-check-circle fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">{{ $stats['paye'] }}</h3>
                <p class="text-muted small mb-0">Payées</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                <i class="bi bi-x-circle fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">{{ $stats['non_paye'] }}</h3>
                <p class="text-muted small mb-0">Non payées</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ RECHERCHE PAR RÉFÉRENCE ═══════════ --}}
<div class="content-card p-4 mb-4">
    <h5 class="fw-bold mb-3">
        <i class="bi bi-search text-primary"></i> Rechercher une demande
    </h5>
    <form method="GET" action="{{ route('admin.paiements.index') }}" class="row g-2">
        <div class="col-md-9">
            <input type="text"
                   name="reference"
                   class="form-control form-control-lg"
                   placeholder="Entrez la référence (ex: 6AA958)"
                   value="{{ $reference }}"
                   autofocus>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-search"></i> Rechercher
            </button>
        </div>
    </form>
</div>

{{-- ═══════════ RÉSULTATS ═══════════ --}}
@if($reference)

    @if($demandes && $demandes->count() > 0)
        @foreach($demandes as $demande)

            {{-- ✅ CARTE DEMANDE --}}
            <div class="content-card mb-4 p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            Référence : {{ $demande->reference }}
                        </h4>
                        <small class="text-muted">
                            #{{ $demande->id_demande }} — {{ $demande->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>

                    {{-- ✅ BADGE STATUT PAIEMENT --}}
                    @if($demande->est_paye)
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-check-circle"></i> PAYÉ
                        </span>
                    @else
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="bi bi-x-circle"></i> NON PAYÉ
                        </span>
                    @endif
                </div>

                <hr>

                {{-- ✅ INFOS DEMANDEUR --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="bi bi-person"></i> Demandeur
                        </h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td width="140"><strong>Nom :</strong></td><td>{{ $demande->demandeur_nom }}</td></tr>
                            <tr><td><strong>Prénom :</strong></td><td>{{ $demande->demandeur_prenom }}</td></tr>
                            <tr><td><strong>Contact :</strong></td><td>{{ $demande->demandeur_contact }}</td></tr>
                            <tr><td><strong>Adresse :</strong></td><td>{{ $demande->demandeur_adresse }}</td></tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="bi bi-file-text"></i> Informations
                        </h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td width="140"><strong>Service :</strong></td>
                                <td>
                                    @if($demande->service === 'express')
                                        <span class="badge bg-warning text-dark">⚡ Express</span>
                                    @else
                                        <span class="badge bg-secondary">🛡 Standard</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><td><strong>Nombre d'actes :</strong></td><td>{{ $demande->nombre_actes }}</td></tr>
                            <tr><td><strong>Date traitement :</strong></td>
                                <td>
                                    {{ $demande->date_traitement
                                        ? \Carbon\Carbon::parse($demande->date_traitement)->format('d/m/Y H:i')
                                        : '—' }}
                                </td>
                            </tr>
                            @if($demande->est_paye)
                                <tr><td><strong>Encaissé par :</strong></td>
                                    <td>{{ $demande->traiteur->name ?? 'N/A' }}</td>
                                </tr>
                                <tr><td><strong>Date paiement :</strong></td>
                                    <td>{{ $demande->date_paiement ? $demande->date_paiement->format('d/m/Y H:i') : '—' }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- ✅ MONTANT TOTAL --}}
                <div style="
                    padding: 20px;
                    background: linear-gradient(135deg, #EEF2FF, #DBEAFE);
                    border: 2px solid #4F46E5;
                    border-radius: 12px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 20px;
                ">
                    <div>
                        <div class="text-muted small mb-1">MONTANT À PAYER</div>
                        <div style="font-size: 32px; font-weight: 900; color: #4F46E5;">
                            {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar
                        </div>
                    </div>
                    <div>
                        <i class="bi bi-cash-stack" style="font-size: 48px; color: #4F46E5; opacity: 0.3;"></i>
                    </div>
                </div>

                {{-- ✅ ACTIONS --}}
                <div class="text-end">
                    @if(!$demande->est_paye)
                        <form action="{{ route('admin.paiements.marquer-paye', $demande->id_demande) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Confirmer l\'encaissement en espèces de {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar ?');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="bi bi-cash-coin"></i> Marquer comme PAYÉ (espèces)
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.paiements.annuler', $demande->id_demande) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Annuler ce paiement ?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-arrow-counterclockwise"></i> Annuler le paiement
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        @endforeach
    @else
        <div class="content-card p-5 text-center">
            <i class="bi bi-search" style="font-size: 64px; color: #9CA3AF;"></i>
            <h5 class="mt-3 text-muted">Aucune demande trouvée</h5>
            <p class="text-muted mb-0">
                Aucune demande acceptée ne correspond à la référence <strong>{{ $reference }}</strong>.
            </p>
        </div>
    @endif

@else
    {{-- ✅ AVANT RECHERCHE --}}
    <div class="content-card p-5 text-center">
        <i class="bi bi-upc-scan" style="font-size: 64px; color: #9CA3AF;"></i>
        <h5 class="mt-3 text-muted">Entrez une référence pour commencer</h5>
        <p class="text-muted mb-0">
            Demandez au citoyen de présenter son <strong>document PDF</strong>, puis saisissez la référence affichée.
        </p>
    </div>
@endif

@endsection