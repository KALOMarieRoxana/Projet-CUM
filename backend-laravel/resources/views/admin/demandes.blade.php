@extends('layouts.admin')

@section('title', 'Demandes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Demandes en attente</h4>
        <small class="text-muted">Validez ou refusez les demandes des citoyens</small>
    </div>
    <nav>
        <span class="text-muted">Maison</span> &gt; <span>Demandes</span>
    </nav>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-ticket-perforated"></i></div>
            <div>
                <h3>{{ $statistiques['total'] ?? 0 }}</h3>
                <p>Total des demandes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff7ed;color:#ea580c;"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <h3>{{ $statistiques['en_attente'] ?? 0 }}</h3>
                <p>En attente</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;color:#059669;"><i class="bi bi-check-circle"></i></div>
            <div>
                <h3>{{ $statistiques['acceptee'] ?? $statistiques['acceptée'] ?? 0 }}</h3>
                <p>Acceptées</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-x-circle"></i></div>
            <div>
                <h3>{{ $statistiques['refusee'] ?? $statistiques['refusée'] ?? 0 }}</h3>
                <p>Refusées</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="content-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('admin.demandes') }}" class="row g-2">
            <div class="col-md-3">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    <option value="en attente" {{ request('statut') == 'en attente' ? 'selected' : '' }}>En attente</option>
                    <option value="acceptée" {{ in_array(request('statut'), ['acceptée', 'acceptee']) ? 'selected' : '' }}>Acceptées</option>
                    <option value="refusée" {{ in_array(request('statut'), ['refusée', 'refusee']) ? 'selected' : '' }}>Refusées</option>
                    <option value="partiellement_traitée" {{ request('statut') == 'partiellement_traitée' ? 'selected' : '' }}>Partiellement traitées</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous les types</option>
                    @foreach($typesActes ?? [] as $type)
                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                            {{ $type->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Référence, nom..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Tableau des demandes -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
        <div>
            <h5 class="mb-0">Liste des demandes</h5>
            <small class="text-muted">{{ isset($demandes) ? $demandes->count() : 0 }} demande(s) au total</small>
        </div>
        <div class="input-group" style="width: 280px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Rechercher...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0" id="demandesTable">
            <thead>
                <tr class="text-muted" style="font-size: 0.85rem;">
                    <th class="ps-3">Référence</th>
                    <th>Citoyen</th>
                    <th>Demandeur</th>
                    <th>Actes demandés</th>
                    <th>Service</th>
                    <th>Prix total</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($demandes ?? [] as $demande)
                    <tr>
                        <td class="ps-3">
                            <strong>{{ $demande->numero_reference }}</strong>
                            <br>
                            <small class="text-muted">#{{ $demande->id_demande }}</small>
                        </td>
                        <td>
                            {{ $demande->citoyen->prenom ?? '-' }} {{ $demande->citoyen->nom ?? '-' }}<br>
                            <small class="text-muted">{{ $demande->citoyen->email ?? '' }}</small>
                        </td>
                        <td>
                            {{ $demande->demandeur_prenom }} {{ $demande->demandeur_nom }}<br>
                            <small class="text-muted">{{ $demande->demandeur_contact }}</small>
                        </td>
                        <td>
                            @foreach($demande->items ?? [] as $item)
                                <span class="badge bg-info">
                                    {{ $item->typeActe->nom ?? 'Inconnu' }} 
                                    <span class="badge bg-light text-dark">x{{ $item->quantite }}</span>
                                </span>
                                <br>
                            @endforeach
                            <small class="text-muted">{{ $demande->items ? $demande->items->count() : 0 }} acte(s) demandé(s)</small>
                        </td>
                        <td>
                            @if($demande->service == 'express')
                                <span class="badge bg-warning">⚡ Express</span>
                            @else
                                <span class="badge bg-secondary">Standard</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ number_format($demande->prix_total, 0, ',', ' ') }} FCFA</strong>
                        </td>
                        <td>
                            @if($demande->statut == 'en attente')
                                <span class="badge bg-warning">⏳ En attente</span>
                            @elseif(in_array($demande->statut, ['acceptée', 'acceptee']))
                                <span class="badge bg-success">✅ Acceptée</span>
                            @elseif(in_array($demande->statut, ['refusée', 'refusee']))
                                <span class="badge bg-danger">❌ Refusée</span>
                            @elseif($demande->statut == 'partiellement_traitée')
                                <span class="badge bg-info">🔄 Partielle</span>
                            @endif
                        </td>
                        <td>{{ $demande->created_at ? $demande->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="text-end pe-3">
                            <!-- Bouton Détails -->
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $demande->id_demande }}">
                                <i class="bi bi-eye"></i>
                            </button>
                            
                            <!-- Actions de traitement -->
                            @if($demande->statut == 'en attente')
                                <form action="{{ route('admin.demandes.traiter', $demande->id_demande) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="accepter">
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Valider toutes les demandes de cette référence ?')">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.demandes.traiter', $demande->id_demande) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="refuser">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Refuser toutes les demandes de cette référence ?')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            Aucune demande trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if(isset($demandes) && method_exists($demandes, 'links'))
        <div class="p-3 border-top">
            {{ $demandes->links() }}
        </div>
    @endif
</div>

<!-- ========================================================== -->
<!-- MODALS POUR LES DÉTAILS                                     -->
<!-- ========================================================== -->
@foreach($demandes ?? [] as $demande)
<div class="modal fade" id="detailsModal{{ $demande->id_demande }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    📌 Demande #{{ $demande->numero_reference }}
                    <span class="badge bg-secondary ms-2">{{ $demande->items ? $demande->items->count() : 0 }} acte(s)</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <!-- Informations générales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6><i class="bi bi-person"></i> Demandeur</h6>
                        <table class="table table-sm table-borderless">
                            <tr><td width="120"><strong>Nom:</strong></td><td>{{ $demande->demandeur_nom }}</td></tr>
                            <tr><td><strong>Prénom:</strong></td><td>{{ $demande->demandeur_prenom }}</td></tr>
                            <tr><td><strong>Adresse:</strong></td><td>{{ $demande->demandeur_adresse }}</td></tr>
                            <tr><td><strong>Contact:</strong></td><td>{{ $demande->demandeur_contact }}</td></tr>
                            <tr><td><strong>Relation:</strong></td><td>{{ $demande->demandeur_relation ?? 'Non spécifiée' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-info-circle"></i> Informations</h6>
                        <table class="table table-sm table-borderless">
                            <tr><td width="120"><strong>Référence:</strong></td><td>{{ $demande->numero_reference }}</td></tr>
                            <tr><td><strong>Service:</strong></td><td>{{ ucfirst($demande->service) }}</td></tr>
                            <tr><td><strong>Prix total:</strong></td><td><strong>{{ number_format($demande->prix_total, 0, ',', ' ') }} FCFA</strong></td></tr>
                            <tr><td><strong>Statut:</strong></td><td>
                                @if($demande->statut == 'en attente')
                                    <span class="badge bg-warning">⏳ En attente</span>
                                @elseif(in_array($demande->statut, ['acceptée', 'acceptee']))
                                    <span class="badge bg-success">✅ Acceptée</span>
                                @elseif(in_array($demande->statut, ['refusée', 'refusee']))
                                    <span class="badge bg-danger">❌ Refusée</span>
                                @elseif($demande->statut == 'partiellement_traitée')
                                    <span class="badge bg-info">🔄 Partiellement traitée</span>
                                @endif
                            </td></tr>
                            <tr><td><strong>Date:</strong></td><td>{{ $demande->created_at ? $demande->created_at->format('d/m/Y H:i') : '-' }}</td></tr>
                            @if($demande->traitePar)
                                <tr><td><strong>Traité par:</strong></td><td>{{ $demande->traitePar->name ?? '' }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                <hr>

                <!-- Détails des actes -->
                <h6 class="mb-3"><i class="bi bi-list-ul"></i> Actes demandés</h6>
                @foreach($demande->items ?? [] as $index => $item)
                    <div class="card mb-3 {{ in_array($item->statut, ['acceptée', 'acceptee']) ? 'border-success' : (in_array($item->statut, ['refusée', 'refusee']) ? 'border-danger' : 'border-warning') }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">
                                    {{ $index + 1 }}. {{ $item->typeActe->nom ?? 'Inconnu' }}
                                    <span class="badge bg-secondary">x{{ $item->quantite }}</span>
                                    <span class="badge bg-info">{{ number_format($item->prix_total, 0, ',', ' ') }} FCFA</span>
                                </h6>
                                <div>
                                    @if($item->statut == 'en attente')
                                        <span class="badge bg-warning">⏳ En attente</span>
                                    @elseif(in_array($item->statut, ['acceptée', 'acceptee']))
                                        <span class="badge bg-success">✅ Acceptée</span>
                                    @elseif(in_array($item->statut, ['refusée', 'refusee']))
                                        <span class="badge bg-danger">❌ Refusée</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Détails spécifiques selon le type -->
                            @php
                                $details = $item->details;
                                $nomType = strtolower($item->typeActe->nom ?? '');
                            @endphp
                            
                            <div class="mt-2" style="font-size: 0.9rem;">
                                @if(str_contains($nomType, 'naissance') && $details)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Personne:</strong> {{ $details->personne_prenom ?? '' }} {{ $details->personne_nom ?? '' }}
                                            ({{ $details->personne_sexe ?? '' }})<br>
                                            <strong>Né(e) le:</strong> {{ $details->personne_date_naissance ? \Carbon\Carbon::parse($details->personne_date_naissance)->format('d/m/Y') : '' }}
                                            à {{ $details->personne_lieu_naissance ?? '' }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Père:</strong> {{ $details->pere_prenom ?? '' }} {{ $details->pere_nom ?? '' }}<br>
                                            <strong>Mère:</strong> {{ $details->mere_prenom ?? '' }} {{ $details->mere_nom ?? '' }}
                                            @if($details->personne_numero_acte)
                                                <br><strong>Numéro acte:</strong> {{ $details->personne_numero_acte }}
                                            @endif
                                        </div>
                                    </div>
                                    
                                @elseif(str_contains($nomType, 'mariage') && $details)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Époux:</strong> {{ $details->epoux_prenom ?? '' }} {{ $details->epoux_nom ?? '' }}<br>
                                            <strong>Né(e) le:</strong> {{ $details->epoux_date_naissance ? \Carbon\Carbon::parse($details->epoux_date_naissance)->format('d/m/Y') : '' }}
                                            à {{ $details->epoux_lieu_naissance ?? '' }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Épouse:</strong> {{ $details->epouse_prenom ?? '' }} {{ $details->epouse_nom ?? '' }}<br>
                                            <strong>Né(e) le:</strong> {{ $details->epouse_date_naissance ? \Carbon\Carbon::parse($details->epouse_date_naissance)->format('d/m/Y') : '' }}
                                            à {{ $details->epouse_lieu_naissance ?? '' }}
                                        </div>
                                        <div class="col-md-12 mt-1">
                                            <strong>Mariage:</strong> {{ $details->date_mariage ? \Carbon\Carbon::parse($details->date_mariage)->format('d/m/Y') : '' }}
                                            à {{ $details->lieu_mariage ?? '' }}
                                        </div>
                                    </div>
                                    
                                @elseif(str_contains($nomType, 'décès') || str_contains($nomType, 'deces') && $details)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Défunt:</strong> {{ $details->defunt_prenom ?? '' }} {{ $details->defunt_nom ?? '' }}<br>
                                            <strong>Né(e) le:</strong> {{ $details->defunt_date_naissance ? \Carbon\Carbon::parse($details->defunt_date_naissance)->format('d/m/Y') : '' }}
                                            à {{ $details->defunt_lieu_naissance ?? '' }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Décès:</strong> {{ $details->date_deces ? \Carbon\Carbon::parse($details->date_deces)->format('d/m/Y') : '' }}
                                            à {{ $details->lieu_deces ?? '' }}<br>
                                            <strong>Cause:</strong> {{ $details->cause_deces ?? 'Non spécifiée' }}
                                        </div>
                                    </div>
                                    
                                @elseif(str_contains($nomType, 'divorce') && $details)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Conjoint:</strong> {{ $details->conjoint_prenom ?? '' }} {{ $details->conjoint_nom ?? '' }}<br>
                                            <strong>Conjointe:</strong> {{ $details->conjointe_prenom ?? '' }} {{ $details->conjointe_nom ?? '' }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Mariage:</strong> {{ $details->date_mariage ? \Carbon\Carbon::parse($details->date_mariage)->format('d/m/Y') : '' }}<br>
                                            <strong>Demande:</strong> {{ $details->date_demande_divorce ? \Carbon\Carbon::parse($details->date_demande_divorce)->format('d/m/Y') : '' }}<br>
                                            <strong>Motif:</strong> {{ $details->motif ?? 'Non spécifié' }}
                                        </div>
                                    </div>
                                @else
                                    <em class="text-muted">Aucun détail disponible</em>
                                @endif
                            </div>
                            
                            <!-- Actions sur l'item individuel -->
                            @if($item->statut == 'en attente' && ($demande->statut == 'en attente' || $demande->statut == 'partiellement_traitée'))
                                <div class="mt-2">
                                    <form action="{{ route('admin.demandes.traiter-item', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="action" value="accepter">
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Accepter cet acte ?')">
                                            <i class="bi bi-check-lg"></i> Accepter l'acte
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.demandes.traiter-item', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="action" value="refuser">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Refuser cet acte ?')">
                                            <i class="bi bi-x-lg"></i> Refuser l'acte
                                        </button>
                                    </form>
                                    @if($details && isset($details->id))
                                        <a href="{{ route('admin.demandes.details', ['type' => $item->typeActe->nom, 'id' => $details->id]) }}" class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i> Voir détail
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                
                <!-- Résumé des statuts -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-secondary">
                            <strong>Résumé :</strong>
                            @php
                                $totalItems = $demande->items ? $demande->items->count() : 0;
                                $acceptes = $demande->items ? $demande->items->filter(fn($i) => in_array($i->statut, ['acceptée', 'acceptee']))->count() : 0;
                                $refuses = $demande->items ? $demande->items->filter(fn($i) => in_array($i->statut, ['refusée', 'refusee']))->count() : 0;
                                $enAttente = $demande->items ? $demande->items->where('statut', 'en attente')->count() : 0;
                            @endphp
                            <span class="badge bg-secondary">{{ $totalItems }} total</span>
                            <span class="badge bg-success">{{ $acceptes }} acceptés</span>
                            <span class="badge bg-danger">{{ $refuses }} refusés</span>
                            <span class="badge bg-warning">{{ $enAttente }} en attente</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                @if($demande->statut == 'en attente' || $demande->statut == 'partiellement_traitée')
                    <form action="{{ route('admin.demandes.traiter', $demande->id_demande) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="accepter">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Valider toutes les demandes ?')">
                            <i class="bi bi-check-all"></i> Tout valider
                        </button>
                    </form>
                    <form action="{{ route('admin.demandes.traiter', $demande->id_demande) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="refuser">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Refuser toutes les demandes ?')">
                            <i class="bi bi-x-circle"></i> Tout refuser
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<script>
    // Recherche dynamique dans le tableau
    document.getElementById('searchInput').addEventListener('input', function () {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#demandesTable tbody tr');
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

    // Support pour l'attribut data-confirm personnalisé si nécessaire
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form[data-confirm]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm(this.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush