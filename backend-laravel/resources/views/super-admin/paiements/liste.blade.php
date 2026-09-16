@extends('layouts.admin')

@section('title', 'Liste des paiements')

@section('content')

{{-- ═══════════ HEADER ═══════════ --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Liste des paiements</h4>
        <small class="text-muted">Historique des paiements par mois</small>
    </div>
    <nav>
        <span class="text-muted">Maison</span> &gt;
        <a href="{{ route('super-admin.paiements.index') }}" class="text-decoration-none">Paiement</a> &gt;
        <span>Liste</span>
    </nav>
</div>

{{-- ═══════════ BOUTON RETOUR ═══════════ --}}
<div class="mb-3">
    <a href="{{ route('super-admin.paiements.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour à la recherche
    </a>
</div>

<div class="row g-4">

    {{-- ═══════════ COLONNE GAUCHE : LISTE DES MOIS ═══════════ --}}
    <div class="col-md-3">
        <div class="content-card p-3">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-calendar3 text-primary"></i> Mois
            </h6>

            <div style="max-height: 600px; overflow-y: auto;">
                @foreach($listeMois as $m)
                    <a href="{{ route('super-admin.paiements.liste', ['mois' => $m['valeur']]) }}"
                       style="text-decoration: none; display: block;">
                        <div style="
                            padding: 10px 12px;
                            border-radius: 8px;
                            margin-bottom: 4px;
                            background: {{ $moisSelectionne === $m['valeur'] ? '#EEF2FF' : 'transparent' }};
                            border: 1px solid {{ $moisSelectionne === $m['valeur'] ? '#4F46E5' : 'transparent' }};
                            color: {{ $moisSelectionne === $m['valeur'] ? '#4F46E5' : '#374151' }};
                            transition: all 0.2s;
                        ">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 13px; font-weight: 600;">
                                        {{ ucfirst($m['label']) }}
                                    </div>
                                    <small style="font-size: 11px; color: #6B7280;">
                                        {{ $m['count'] }} paiement{{ $m['count'] > 1 ? 's' : '' }}
                                    </small>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 11px; color: {{ $moisSelectionne === $m['valeur'] ? '#4F46E5' : '#10B981' }}; font-weight: 700;">
                                        {{ number_format($m['total'], 0, ',', ' ') }} Ar
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════ COLONNE DROITE : DÉTAILS DU MOIS ═══════════ --}}
    <div class="col-md-9">

        {{-- ✅ TITRE DU MOIS --}}
        <div class="content-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-calendar-check text-primary"></i>
                        {{ ucfirst(\Carbon\Carbon::parse($moisSelectionne . '-01')->translatedFormat('F Y')) }}
                    </h5>
                    <small class="text-muted">
                        {{ $nombreMois }} paiement{{ $nombreMois > 1 ? 's' : '' }} encaissé{{ $nombreMois > 1 ? 's' : '' }}
                    </small>
                </div>

                {{-- ✅ TOTAL DU MOIS --}}
                <div style="
                    padding: 16px 24px;
                    background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
                    border: 2px solid #10B981;
                    border-radius: 12px;
                    text-align: right;
                ">
                    <div style="font-size: 11px; color: #047857; text-transform: uppercase; font-weight: 700;">
                        Total encaissé
                    </div>
                    <div style="font-size: 28px; font-weight: 900; color: #065F46;">
                        {{ number_format($totalMois, 0, ',', ' ') }} Ar
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ TABLEAU DES PAIEMENTS --}}
        <div class="content-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Référence</th>
                            <th>Citoyen</th>
                            <th>Actes</th>
                            <th>Montant</th>
                            <th>Date paiement</th>
                            <th>Encaissé par</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demandes as $demande)
                            <tr>
                                <td class="ps-3">
                                    <strong>{{ $demande->reference }}</strong>
                                    <br>
                                    <small class="text-muted">#{{ $demande->id_demande }}</small>
                                </td>
                                <td>
                                    {{ $demande->demandeur_prenom }} {{ $demande->demandeur_nom }}
                                    <br>
                                    <small class="text-muted">{{ $demande->citoyen->email ?? '' }}</small>
                                </td>
                                <td>
                                    @foreach($demande->demandeActes as $item)
                                        <span class="badge bg-info text-dark mb-1">
                                            {{ $item->typeActe->nom ?? 'Acte' }}
                                            × {{ $item->quantite }}
                                        </span>
                                        <br>
                                    @endforeach
                                </td>
                                <td>
                                    <strong style="color: #10B981;">
                                        {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar
                                    </strong>
                                </td>
                                <td>
                                    {{ $demande->date_paiement
                                        ? \Carbon\Carbon::parse($demande->date_paiement)->format('d/m/Y H:i')
                                        : '—' }}
                                </td>
                                <td>
                                    {{ $demande->traiteur->name ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Aucun paiement pour ce mois
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($demandes->count() > 0)
                        <tfoot>
                            <tr style="background: #F3F4F6; font-weight: 700;">
                                <td colspan="3" class="text-end fs-6">TOTAL DU MOIS</td>
                                <td style="font-size: 18px; color: #4F46E5;">
                                    {{ number_format($totalMois, 0, ',', ' ') }} Ar
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</div>

@endsection