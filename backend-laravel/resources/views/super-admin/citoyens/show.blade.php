@extends('layouts.admin')

@section('title', 'Détails citoyen')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('super-admin.citoyens.index') }}" class="btn btn-sm btn-light mb-2">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h4 class="mb-0">{{ $citoyen->prenom }} {{ $citoyen->nom }}</h4>
        <small class="text-muted">Détails du citoyen</small>
    </div>
    <div>
        @if($citoyen->actif)
            <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle"></i> Actif</span>
        @else
            <span class="badge bg-danger px-3 py-2"><i class="bi bi-x-circle"></i> Désactivé</span>
        @endif
    </div>
</div>

<div class="row g-3">
    {{-- Infos personnelles --}}
    <div class="col-md-6">
        <div class="content-card p-4">
            <h5 class="fw-bold mb-3">👤 Informations personnelles</h5>
            <table class="table table-sm">
                <tr><th width="150">Nom :</th><td>{{ $citoyen->nom }}</td></tr>
                <tr><th>Prénom :</th><td>{{ $citoyen->prenom }}</td></tr>
                <tr><th>Email :</th><td>{{ $citoyen->email }}</td></tr>
                <tr><th>Contact :</th><td>{{ $citoyen->contact ?? '—' }}</td></tr>
                <tr><th>Adresse :</th><td>{{ $citoyen->adresse ?? '—' }}</td></tr>
                <tr><th>Relation :</th><td>{{ $citoyen->relation ?? '—' }}</td></tr>
                <tr><th>Inscription :</th><td>{{ $citoyen->created_at->format('d/m/Y à H:i') }}</td></tr>
                @if(!$citoyen->actif && $citoyen->desactive_le)
                    <tr>
                        <th>Désactivé le :</th>
                        <td>{{ $citoyen->desactive_le->format('d/m/Y à H:i') }}</td>
                    </tr>
                    @if($citoyen->raison_desactivation)
                        <tr>
                            <th>Raison :</th>
                            <td class="text-danger">{{ $citoyen->raison_desactivation }}</td>
                        </tr>
                    @endif
                @endif
            </table>
        </div>
    </div>

    {{-- Pièces d'identité --}}
    <div class="col-md-6">
        <div class="content-card p-4">
            <h5 class="fw-bold mb-3">🆔 Pièces d'identité</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small mb-1">CIN Recto</div>
                    @if($citoyen->cin_recto)
                        <img src="{{ asset('storage/' . $citoyen->cin_recto) }}"
                             alt="CIN Recto"
                             class="img-fluid rounded border"
                             style="max-height: 200px; cursor: pointer;"
                             onclick="window.open(this.src, '_blank')">
                    @else
                        <div class="text-muted">—</div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="text-muted small mb-1">CIN Verso</div>
                    @if($citoyen->cin_verso)
                        <img src="{{ asset('storage/' . $citoyen->cin_verso) }}"
                             alt="CIN Verso"
                             class="img-fluid rounded border"
                             style="max-height: 200px; cursor: pointer;"
                             onclick="window.open(this.src, '_blank')">
                    @else
                        <div class="text-muted">—</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Demandes du citoyen --}}
    <div class="col-md-12">
        <div class="content-card p-4">
            <h5 class="fw-bold mb-3">📋 Demandes du citoyen ({{ $citoyen->demandes->count() }})</h5>

            @if($citoyen->demandes->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Référence</th>
                                <th>Actes</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($citoyen->demandes as $demande)
                                <tr>
                                    <td><strong>{{ $demande->reference }}</strong></td>
                                    <td>
                                        @foreach($demande->demandeActes as $acte)
                                            <div style="font-size: 12px;">
                                                📄 {{ $acte->typeActe->nom ?? 'Acte' }} × {{ $acte->quantite }}
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <strong>{{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar</strong>
                                    </td>
                                    <td>
                                        @if($demande->statut === 'acceptée' || $demande->statut === 'acceptee')
                                            <span class="badge bg-success">Acceptée</span>
                                        @elseif($demande->statut === 'refusée' || $demande->statut === 'refusee')
                                            <span class="badge bg-danger">Refusée</span>
                                        @elseif($demande->statut === 'archivée')
                                            <span class="badge bg-secondary">Archivée</span>
                                        @else
                                            <span class="badge bg-warning">En attente</span>
                                        @endif
                                    </td>
                                    <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('super-admin.demandes.show', $demande->id_demande) }}"
                                           class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Aucune demande
                </div>
            @endif
        </div>
    </div>
</div>

@endsection