@extends('layouts.admin')

@section('title', 'Détail demande ' . $demande->reference)

@section('content')

{{-- ═══════════ HEADER ═══════════ --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-light mb-2">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h4 class="fw-bold mb-1">Demande {{ $demande->reference }}</h4>
        <p class="text-muted small mb-0">Détail complet de la demande</p>
    </div>
    <div>
        @if($demande->statut === 'acceptée' || $demande->statut === 'acceptee')
            <span class="badge bg-success px-3 py-2">
                <i class="bi bi-check-circle"></i> Acceptée
            </span>
        @elseif($demande->statut === 'refusée' || $demande->statut === 'refusee')
            <span class="badge bg-danger px-3 py-2">
                <i class="bi bi-x-circle"></i> Refusée
            </span>
        @else
            <span class="badge bg-warning text-dark px-3 py-2">
                <i class="bi bi-hourglass-split"></i> En attente
            </span>
        @endif
    </div>
</div>

{{-- ═══════════ LIGNE 1 : INFOS + DEMANDEUR ═══════════ --}}
<div class="row g-3 mb-3">

    {{-- Infos générales --}}
    <div class="col-md-8">
        <div class="content-card h-100">
            <h5 class="fw-bold mb-3">📋 Informations générales</h5>
            <table class="table table-sm mb-0">
                <tr>
                    <th width="200">Référence</th>
                    <td><strong>{{ $demande->reference }}</strong></td>
                </tr>
                <tr>
                    <th>Service</th>
                    <td>
                        @if($demande->service === 'express')
                            <span class="badge bg-warning text-dark">⚡ Express</span>
                        @else
                            <span class="badge bg-secondary">🛡 Standard</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Nombre d'actes</th>
                    <td>{{ $demande->nombre_actes ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Prix total</th>
                    <td><strong>{{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar</strong></td>
                </tr>
                <tr>
                    <th>Date de création</th>
                    <td>{{ $demande->created_at->format('d/m/Y à H:i') }}</td>
                </tr>
                @if($demande->date_traitement)
                    <tr>
                        <th>Date de traitement</th>
                        <td>{{ \Carbon\Carbon::parse($demande->date_traitement)->format('d/m/Y à H:i') }}</td>
                    </tr>
                @endif
                @if($demande->commentaire_admin)
                    <tr>
                        <th>Commentaire admin</th>
                        <td>{{ $demande->commentaire_admin }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Demandeur + Concerné --}}
    <div class="col-md-4">
        <div class="content-card mb-3">
            <h5 class="fw-bold mb-3">👤 Demandeur</h5>
            <p class="mb-2"><strong>Nom :</strong> {{ $demande->demandeur_prenom }} {{ $demande->demandeur_nom }}</p>
            <p class="mb-2"><strong>Contact :</strong> {{ $demande->demandeur_contact ?? '—' }}</p>
            <p class="mb-2"><strong>Adresse :</strong> {{ $demande->demandeur_adresse ?? '—' }}</p>
            <p class="mb-0"><strong>Relation :</strong> {{ $demande->demandeur_relation ?? '—' }}</p>
        </div>

        <div class="content-card">
            <h5 class="fw-bold mb-3">👥 Personne concernée</h5>
            <p class="mb-2"><strong>Nom :</strong> {{ $demande->personne_prenom }} {{ $demande->personne_nom }}</p>
            <p class="mb-2"><strong>Lieu :</strong> {{ $demande->personne_lieu_naissance ?? '—' }}</p>
            <p class="mb-0"><strong>Date :</strong>
                {{ $demande->personne_date_naissance ? \Carbon\Carbon::parse($demande->personne_date_naissance)->format('d/m/Y') : '—' }}
            </p>
        </div>
    </div>
</div>

{{-- ═══════════ LIGNE 2 : ACTES DEMANDÉS (PLEINE LARGEUR) ═══════════ --}}
<div class="content-card">
    <h5 class="fw-bold mb-3">📄 Actes demandés</h5>

    @if($demande->demandeActes && $demande->demandeActes->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 12%;">Référence</th>
                        <th style="width: 18%;">Type d'acte</th>
                        <th style="width: 18%;">Sous-type / Document</th>
                        <th class="text-center" style="width: 8%;">Langue</th>
                        <th class="text-center" style="width: 8%;">Qté acte</th>
                        <th class="text-center" style="width: 8%;">Qté doc</th>
                        <th class="text-center" style="width: 10%;">Service</th>
                        <th class="text-end" style="width: 10%;">Prix unitaire</th>
                        <th class="text-end" style="width: 10%;">Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demande->demandeActes as $index => $item)
                        @php
                            $typeActe = $item->typeActe;
                            $supplement = $item->supplement;
                            $langue = strtoupper($item->langue ?? 'MG');
                            $qteActe = (int)($item->quantite ?? 1);
                            $qteSupp = (int)($item->quantite_supplement ?? 0);
                            $prixActe = (float)($item->prix_acte ?? 0);
                            $prixSupp = (float)($item->prix_supplement ?? 0);
                            $sousTotal = ($prixActe * $qteActe) + ($prixSupp * $qteSupp);
                            $service = $item->type_service ?? $demande->service ?? 'standard';
                            $aSupplement = $supplement && $qteSupp > 0;

                            // ✅ RÉFÉRENCE : la référence de la demande + numéro de ligne
                            $refActe = $demande->reference . '-' . ($index + 1);
                        @endphp
                        <tr>
                            {{-- ✅ Référence --}}
                            <td>
                                <span class="fw-semibold text-dark">{{ $refActe }}</span>
                            </td>

                            {{-- Type d'acte --}}
                            <td>
                                <div class="fw-semibold">{{ $typeActe->nom ?? 'Acte' }}</div>
                            </td>

                            {{-- Sous-type --}}
                            <td>
                                @if($aSupplement)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        📋 {{ $supplement->nom }}
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">— Aucun —</span>
                                @endif
                            </td>

                            {{-- Langue --}}
                            <td class="text-center">
                                @if($langue === 'MG')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">🇲🇬 MG</span>
                                @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary">🇫🇷 FR</span>
                                @endif
                            </td>

                            {{-- Qté acte --}}
                            <td class="text-center fw-bold">{{ $qteActe }}</td>

                            {{-- Qté doc --}}
                            <td class="text-center">
                                @if($aSupplement)
                                    <span class="fw-bold text-primary">+ {{ $qteSupp }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Service --}}
                            <td class="text-center">
                                @if($service === 'express')
                                    <span class="badge bg-warning text-dark">⚡ Express</span>
                                @else
                                    <span class="badge bg-secondary">🛡 Standard</span>
                                @endif
                            </td>

                            {{-- Prix unitaire --}}
                            <td class="text-end">
                                @if($aSupplement)
                                    <div>{{ number_format($prixActe, 0, ',', ' ') }} Ar</div>
                                    <small class="text-primary">
                                        + {{ number_format($prixSupp, 0, ',', ' ') }} Ar
                                    </small>
                                @else
                                    {{ number_format($prixActe, 0, ',', ' ') }} Ar
                                @endif
                            </td>

                            {{-- Sous-total --}}
                            <td class="text-end fw-bold text-primary">
                                {{ number_format($sousTotal, 0, ',', ' ') }} Ar
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="8" class="text-end fw-bold fs-6">TOTAL GÉNÉRAL</td>
                        <td class="text-end fw-bold text-primary fs-5">
                            {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="text-center py-4 text-muted">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            Aucun acte associé à cette demande
        </div>
    @endif
</div>

@endsection