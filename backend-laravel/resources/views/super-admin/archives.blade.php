@extends('layouts.admin')

@section('title', 'Archives des demandes')

@push('styles')
<style>
    .archive-card {
        background: #FFF;
        border-radius: 14px;
        border: 1px solid #E5E7EB;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .archive-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        border-color: #F59E0B;
    }
    .archive-card-icon {
        width: 64px;
        height: 64px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .archive-card-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .archive-card-body {
        flex: 1;
    }
    .archive-card-value {
        font-size: 32px;
        font-weight: 800;
        color: #111827;
        line-height: 1;
        margin-bottom: 6px;
    }
    .archive-card-label {
        font-size: 13px;
        color: #6B7280;
        font-weight: 500;
    }

    /* Modal */
    .archive-modal .modal-dialog {
        max-width: 1100px;
    }
    .archive-modal .modal-header {
        background: linear-gradient(135deg, #F59E0B, #F97316);
        color: #FFF;
    }
    .archive-modal .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
    .archive-modal-total {
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Archives des demandes</h4>
        <small class="text-muted">Consultez les demandes archivées par type</small>
    </div>
    <nav>
        <span class="text-muted">Maison</span> &gt; <span>Super Admin</span> &gt; <span>Archives</span>
    </nav>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- 3 CARTES                                                    --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- 📦 TOTAL --}}
    <div class="col-md-4">
        <div class="archive-card">
            <div class="archive-card-icon">
                <i class="bi bi-archive-fill" style="font-size: 48px; color: #4F46E5;"></i>
            </div>
            <div class="archive-card-body">
                <div class="archive-card-value">{{ $stats['total'] }}</div>
                <div class="archive-card-label">Total archivées</div>
            </div>
        </div>
    </div>

    {{-- 📦 ACCEPTÉES — avec l'icône dossier orange --}}
    <div class="col-md-4">
        <div class="archive-card" data-bs-toggle="modal" data-bs-target="#modalArchiveesAcceptees">
            <div class="archive-card-icon">
                {{-- ✅ L'icône dossier orange (voir étape 5 pour l'image) --}}
                <img src="{{ asset('images/archive-folder.png') }}" alt="Archives acceptées">
            </div>
            <div class="archive-card-body">
                <div class="archive-card-value" style="color: #F59E0B;">{{ $stats['acceptees'] }}</div>
                <div class="archive-card-label">Archivées acceptées</div>
            </div>
        </div>
    </div>

    {{-- 📦 REFUSÉES --}}
    <div class="col-md-4">
        <div class="archive-card" data-bs-toggle="modal" data-bs-target="#modalArchiveesRefusees">
            <div class="archive-card-icon">
                <i class="bi bi-archive-fill" style="font-size: 48px; color: #DC2626;"></i>
            </div>
            <div class="archive-card-body">
                <div class="archive-card-value" style="color: #DC2626;">{{ $stats['refusees'] }}</div>
                <div class="archive-card-label">Archivées refusées</div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODAL 1 : ARCHIVÉES ACCEPTÉES                               --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal fade archive-modal" id="modalArchiveesAcceptees" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Demandes archivées — Acceptées
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="archive-modal-total">
                    <span style="font-weight: 600;">Total :</span>
                    <span style="font-size: 18px; font-weight: 800; color: #F59E0B;">
                        {{ $archiveesAcceptees->count() }} demande(s)
                    </span>
                </div>

                @if($archiveesAcceptees->count() > 0)
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Référence</th>
                                    <th>Citoyen</th>
                                    <th>Actes</th>
                                    <th>Prix</th>
                                    <th>Archivée le</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($archiveesAcceptees as $demande)
                                    @php
                                        $items = $demande->demandeActes ?? $demande->items ?? collect();
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <strong>{{ $demande->reference }}</strong>
                                            <br>
                                            <small class="text-muted">#{{ $demande->id_demande }}</small>
                                        </td>
                                        <td>
                                            {{ $demande->demandeur_prenom }} {{ $demande->demandeur_nom }}
                                            <br>
                                            <small class="text-muted">{{ $demande->demandeur_contact }}</small>
                                        </td>
                                        <td>
                                            @foreach($items as $item)
                                                <div style="font-size: 12px; color: #374151; font-weight: 500;">
                                                    📄 {{ $item->typeActe->nom ?? $item->type_acte ?? 'Acte' }}
                                                    <span style="color: #9CA3AF;">× {{ $item->quantite }}</span>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            <strong style="color: #111827;">
                                                {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar
                                            </strong>
                                        </td>
                                        <td>
                                            {{ $demande->updated_at ? $demande->updated_at->format('d/m/Y') : '—' }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $demande->updated_at ? $demande->updated_at->format('H:i') : '' }}
                                            </small>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('super-admin.demandes.show', $demande->id_demande) }}"
                                               class="btn btn-info btn-sm" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <form action="{{ route('super-admin.archives.restaurer', $demande->id_demande) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Restaurer cette demande ?');">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm" title="Restaurer">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-archive display-4 d-block mb-2"></i>
                        Aucune demande archivée (acceptée)
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODAL 2 : ARCHIVÉES REFUSÉES                                --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal fade archive-modal" id="modalArchiveesRefusees" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #EF4444);">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    Demandes archivées — Refusées
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="archive-modal-total">
                    <span style="font-weight: 600;">Total :</span>
                    <span style="font-size: 18px; font-weight: 800; color: #DC2626;">
                        {{ $archiveesRefusees->count() }} demande(s)
                    </span>
                </div>

                @if($archiveesRefusees->count() > 0)
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Référence</th>
                                    <th>Citoyen</th>
                                    <th>Actes</th>
                                    <th>Prix</th>
                                    <th>Archivée le</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($archiveesRefusees as $demande)
                                    @php
                                        $items = $demande->demandeActes ?? $demande->items ?? collect();
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <strong>{{ $demande->reference }}</strong>
                                            <br>
                                            <small class="text-muted">#{{ $demande->id_demande }}</small>
                                        </td>
                                        <td>
                                            {{ $demande->demandeur_prenom }} {{ $demande->demandeur_nom }}
                                            <br>
                                            <small class="text-muted">{{ $demande->demandeur_contact }}</small>
                                        </td>
                                        <td>
                                            @foreach($items as $item)
                                                <div style="font-size: 12px; color: #374151; font-weight: 500;">
                                                    📄 {{ $item->typeActe->nom ?? $item->type_acte ?? 'Acte' }}
                                                    <span style="color: #9CA3AF;">× {{ $item->quantite }}</span>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            <strong style="color: #111827;">
                                                {{ number_format($demande->prix_total ?? 0, 0, ',', ' ') }} Ar
                                            </strong>
                                        </td>
                                        <td>
                                            {{ $demande->updated_at ? $demande->updated_at->format('d/m/Y') : '—' }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $demande->updated_at ? $demande->updated_at->format('H:i') : '' }}
                                            </small>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('super-admin.demandes.show', $demande->id_demande) }}"
                                               class="btn btn-info btn-sm" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <form action="{{ route('super-admin.archives.restaurer', $demande->id_demande) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Restaurer cette demande ?');">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm" title="Restaurer">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-archive display-4 d-block mb-2"></i>
                        Aucune demande archivée (refusée)
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

@endsection