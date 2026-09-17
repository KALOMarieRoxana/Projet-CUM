@extends('layouts.admin')

@section('title', 'Gestion des citoyens')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Gestion des citoyens</h4>
        <small class="text-muted">Gérez les comptes des citoyens inscrits</small>
    </div>
    <nav>
        <span class="text-muted">Maison</span> &gt;
        <span>Super Admin</span> &gt;
        <span>Citoyens</span>
    </nav>
</div>

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

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- 3 CARTES STYLE ABLE PRO                                     --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- 👥 Total citoyens --}}
    <div class="col-md-4">
        @include('partials.stat-card-chart', [
            'label'       => 'Total citoyens',
            'valeur'      => number_format($stats['total'], 0, ',', ' '),
            'pourcentage' => 5.2,
            'couleur'     => '#2563EB',
            'bgCouleur'   => '#DBEAFE',
            'icon'        => 'bi-people-fill',
            'points'      => [10, 8, 14, 12, 18, 16, 20, 25, 22, 26],
        ])
    </div>

    {{-- ✅ Comptes actifs --}}
    <div class="col-md-4">
        @include('partials.stat-card-chart', [
            'label'       => 'Comptes actifs',
            'valeur'      => number_format($stats['actifs'], 0, ',', ' '),
            'pourcentage' => 8.2,
            'couleur'     => '#059669',
            'bgCouleur'   => '#D1FAE5',
            'icon'        => 'bi-check-circle-fill',
            'points'      => [5, 12, 8, 15, 13, 18, 22, 20, 25, 28],
        ])
    </div>

    {{-- 🚫 Comptes désactivés --}}
    <div class="col-md-4">
        @include('partials.stat-card-chart', [
            'label'       => 'Comptes désactivés',
            'valeur'      => number_format($stats['desactives'], 0, ',', ' '),
            'pourcentage' => -3.1,
            'couleur'     => '#EF4444',
            'bgCouleur'   => '#FEE2E2',
            'icon'        => 'bi-x-circle-fill',
            'points'      => [22, 20, 18, 24, 19, 21, 17, 15, 16, 14],
        ])
    </div>
</div>

{{-- ═══════════ FILTRES ═══════════ --}}
<div class="content-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('super-admin.citoyens.index') }}" class="row g-2">
            <div class="col-md-3">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actifs</option>
                    <option value="desactive" {{ request('statut') == 'desactive' ? 'selected' : '' }}>Désactivés</option>
                </select>
            </div>
            <div class="col-md-7">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Nom, prénom, email, contact..."
                       value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Rechercher
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════ TABLEAU ═══════════ --}}
<div class="content-card">
    <div class="p-3 border-bottom">
        <h5 class="mb-0">Liste des citoyens</h5>
        <small class="text-muted">{{ $citoyens->total() }} citoyen(s) au total</small>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Nom & Prénom</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Inscription</th>
                    <th>Statut</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($citoyens as $citoyen)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold">{{ $citoyen->prenom }} {{ $citoyen->nom }}</div>
                            <small class="text-muted">#{{ $citoyen->id_citoyens }}</small>
                        </td>
                        <td>{{ $citoyen->email }}</td>
                        <td>{{ $citoyen->contact ?? '—' }}</td>
                        <td>
                            <div>{{ $citoyen->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $citoyen->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            @if($citoyen->actif)
                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill">
                                    <i class="bi bi-check-circle"></i> Actif
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill">
                                    <i class="bi bi-x-circle"></i> Désactivé
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('super-admin.citoyens.show', $citoyen->id_citoyens) }}"
                               class="btn btn-sm btn-outline-info" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>

                            @if($citoyen->actif)
                                <button type="button"
                                        class="btn btn-sm btn-outline-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDesactiver"
                                        data-id="{{ $citoyen->id_citoyens }}"
                                        data-nom="{{ $citoyen->prenom }} {{ $citoyen->nom }}"
                                        title="Désactiver">
                                    <i class="bi bi-lock"></i>
                                </button>
                            @else
                                <form action="{{ route('super-admin.citoyens.reactiver', $citoyen->id_citoyens) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Réactiver ce compte ?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Réactiver">
                                        <i class="bi bi-unlock"></i>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('super-admin.citoyens.destroy', $citoyen->id_citoyens) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('⚠️ ATTENTION : Cette action supprimera définitivement le citoyen ET toutes ses demandes. Continuer ?');">
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
                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                            Aucun citoyen trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($citoyens->hasPages())
        <div class="p-3 border-top d-flex justify-content-center">
            {{ $citoyens->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

{{-- ═══════════ MODAL DÉSACTIVER ═══════════ --}}
<div class="modal fade" id="modalDesactiver" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="" id="formDesactiver">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        Désactiver le compte
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous désactiver le compte de <strong id="nomCitoyen"></strong> ?</p>
                    <p class="text-muted small">Le citoyen ne pourra plus se connecter.</p>

                    <label for="raison" class="form-label small fw-bold">Raison (optionnel)</label>
                    <textarea name="raison" id="raison" class="form-control" rows="3"
                              placeholder="Ex: Comportement suspect, fausse identité..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-lock"></i> Désactiver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalDesactiver = document.getElementById('modalDesactiver');

    modalDesactiver.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const id = btn.getAttribute('data-id');
        const nom = btn.getAttribute('data-nom');

        document.getElementById('formDesactiver').action = `/super-admin/citoyens/${id}/desactiver`;
        document.getElementById('nomCitoyen').textContent = nom;
    });
});
</script>
@endpush