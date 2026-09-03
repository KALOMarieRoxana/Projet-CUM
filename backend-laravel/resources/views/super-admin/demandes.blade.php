@extends('layouts.admin')

@section('title', 'Toutes les demandes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Toutes les demandes</h4>
        <small class="text-muted">Vue complète, tous statuts confondus</small>
    </div>
    <nav>
        <span class="text-muted">Maison</span> &gt; <span>Super Admin</span> &gt; <span>Demandes</span>
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
                <h3>{{ $statistiques['total'] }}</h3>
                <p>Total des demandes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff7ed;color:#ea580c;"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <h3>{{ $statistiques['en_attente'] }}</h3>
                <p>En attente</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;color:#059669;"><i class="bi bi-check-circle"></i></div>
            <div>
                <h3>{{ $statistiques['acceptee'] }}</h3>
                <p>Acceptées</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-x-circle"></i></div>
            <div>
                <h3>{{ $statistiques['refusee'] }}</h3>
                <p>Refusées</p>
            </div>
        </div>
    </div>
</div>

<!-- Graphique -->
<div class="content-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5 class="mb-0">Analytique</h5>
            <small class="text-muted">Analyse des demandes des 30 derniers jours</small>
        </div>
    </div>
    <canvas id="demandesChart" height="90"></canvas>
</div>

<!-- Filtres -->
<div class="content-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('super-admin.demandes') }}" class="row g-2">
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="en attente" {{ request('statut') == 'en attente' ? 'selected' : '' }}>En attente</option>
                    <option value="acceptée" {{ request('statut') == 'acceptée' ? 'selected' : '' }}>Acceptées</option>
                    <option value="refusée" {{ request('statut') == 'refusée' ? 'selected' : '' }}>Refusées</option>
                    <option value="partiellement_traitée" {{ request('statut') == 'partiellement_traitée' ? 'selected' : '' }}>Partielles</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous types</option>
                    @foreach($typesActes ?? [] as $type)
                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                            {{ $type->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="service" class="form-select form-select-sm">
                    <option value="">Service</option>
                    <option value="standard" {{ request('service') == 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="express" {{ request('service') == 'express' ? 'selected' : '' }}>Express</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Référence, nom, email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrer</button>
                    <a href="{{ route('super-admin.demandes') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tableau des demandes -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom flex-wrap gap-2">
        <div>
            <h5 class="mb-0">Liste complète des demandes</h5>
            <small class="text-muted">{{ $demandes->count() }} demande(s) au total</small>
        </div>

        <div class="d-flex gap-2 align-items-center flex-wrap">
            <!-- Filtres rapides -->
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary btn-sm filter-btn active" data-filter="tous">Tous</button>
                <button type="button" class="btn btn-outline-warning btn-sm filter-btn" data-filter="en attente">En attente</button>
                <button type="button" class="btn btn-outline-success btn-sm filter-btn" data-filter="acceptée">Acceptées</button>
                <button type="button" class="btn btn-outline-danger btn-sm filter-btn" data-filter="refusée">Refusées</button>
                <button type="button" class="btn btn-outline-info btn-sm filter-btn" data-filter="partiellement_traitée">Partielles</button>
            </div>

            <!-- Boutons d'export -->
            <a href="{{ route('super-admin.demandes.export') }}" class="btn btn-success btn-sm">
                <i class="bi bi-download"></i> Export
            </a>

            <div class="input-group" style="width: 200px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Rechercher...">
            </div>
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
                    <th>Traité par</th>
                    <th>Date</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($demandes as $demande)
                    <tr data-statut="{{ $demande->statut }}">
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
                            @foreach($demande->items as $item)
                                <span class="badge bg-info">
                                    {{ $item->typeActe->nom ?? 'Inconnu' }}
                                    <span class="badge bg-light text-dark">x{{ $item->quantite }}</span>
                                </span>
                                <br>
                            @endforeach
                            <small class="text-muted">{{ $demande->items->count() }} acte(s)</small>
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
                            @if ($demande->statut === 'en attente')
                                <span class="badge bg-warning">⏳ En attente</span>
                            @elseif ($demande->statut === 'acceptée')
                                <span class="badge bg-success">✅ Acceptée</span>
                            @elseif ($demande->statut === 'refusée')
                                <span class="badge bg-danger">❌ Refusée</span>
                            @elseif ($demande->statut === 'partiellement_traitée')
                                <span class="badge bg-info">🔄 Partielle</span>
                            @endif
                        </td>
                        <td>
                            @if($demande->traitePar)
                                {{ $demande->traitePar->name ?? '-' }}
                                <br>
                                <small class="text-muted">{{ $demande->date_traitement ? $demande->date_traitement->format('d/m/Y H:i') : '' }}</small>
                            @else
                                <span class="text-muted">Non traité</span>
                            @endif
                        </td>
                        <td>{{ $demande->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-3">
                            <!-- Détails -->
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $demande->id_demande }}">
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- Traiter -->
                            @if($demande->statut == 'en attente' || $demande->statut == 'partiellement_traitée')
                                <a href="{{ route('super-admin.demandes.traiter', $demande->id_demande) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-check2-circle"></i>
                                </a>
                            @endif

                            <!-- Archiver -->
                            @if($demande->statut != 'en attente')
                                <form action="{{ route('super-admin.demandes.archiver', $demande->id_demande) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Archiver cette demande ?')">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
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
<!-- MODALS POUR LES DÉTAILS                                      -->
<!-- ========================================================== -->
@foreach($demandes as $demande)
<div class="modal fade" id="detailsModal{{ $demande->id_demande }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    📌 Demande #{{ $demande->numero_reference }}
                    <span class="badge bg-secondary ms-2">{{ $demande->items->count() }} acte(s)</span>
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
                            <tr><td><strong>Citoyen:</strong></td><td>{{ $demande->citoyen->prenom ?? '' }} {{ $demande->citoyen->nom ?? '' }}</td></tr>
                            <tr><td><strong>Service:</strong></td><td>{{ ucfirst($demande->service) }}</td></tr>
                            <tr><td><strong>Prix total:</strong></td><td><strong>{{ number_format($demande->prix_total, 0, ',', ' ') }} FCFA</strong></td></tr>
                            <tr><td><strong>Statut:</strong></td><td>
                                @if($demande->statut == 'en attente')
                                    <span class="badge bg-warning">⏳ En attente</span>
                                @elseif($demande->statut == 'acceptée')
                                    <span class="badge bg-success">✅ Acceptée</span>
                                @elseif($demande->statut == 'refusée')
                                    <span class="badge bg-danger">❌ Refusée</span>
                                @elseif($demande->statut == 'partiellement_traitée')
                                    <span class="badge bg-info">🔄 Partiellement traitée</span>
                                @endif
                            </td></tr>
                            <tr><td><strong>Date:</strong></td><td>{{ $demande->created_at->format('d/m/Y H:i') }}</td></tr>
                            @if($demande->traitePar)
                                <tr><td><strong>Traité par:</strong></td><td>{{ $demande->traitePar->name ?? '' }}</td></tr>
                                <tr><td><strong>Date traitement:</strong></td><td>{{ $demande->date_traitement ? $demande->date_traitement->format('d/m/Y H:i') : '' }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                <hr>

                <!-- Détails des actes -->
                <h6 class="mb-3"><i class="bi bi-list-ul"></i> Actes demandés</h6>
                @foreach($demande->items as $index => $item)
                    <div class="card mb-3 {{ $item->statut == 'acceptée' ? 'border-success' : ($item->statut == 'refusée' ? 'border-danger' : 'border-warning') }}">
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
                                    @elseif($item->statut == 'acceptée')
                                        <span class="badge bg-success">✅ Acceptée</span>
                                    @elseif($item->statut == 'refusée')
                                        <span class="badge bg-danger">❌ Refusée</span>
                                    @endif
                                    @if($item->traitePar)
                                        <span class="badge bg-secondary">par {{ $item->traitePar->name ?? '' }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Détails spécifiques selon le type -->
                            @php
                                $details = $item->details;
                            @endphp

                            <div class="mt-2" style="font-size: 0.9rem;">
                                @if($item->typeActe->nom == 'naissance' && $details)
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

                                @elseif($item->typeActe->nom == 'mariage' && $details)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Époux:</strong> {{ $details->epoux_prenom ?? '' }} {{ $details->epoux_nom ?? '' }}<br>
                                            <strong>Né le:</strong> {{ $details->epoux_date_naissance ? \Carbon\Carbon::parse($details->epoux_date_naissance)->format('d/m/Y') : '' }}
                                            à {{ $details->epoux_lieu_naissance ?? '' }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Épouse:</strong> {{ $details->epouse_prenom ?? '' }} {{ $details->epouse_nom ?? '' }}<br>
                                            <strong>Née le:</strong> {{ $details->epouse_date_naissance ? \Carbon\Carbon::parse($details->epouse_date_naissance)->format('d/m/Y') : '' }}
                                            à {{ $details->epouse_lieu_naissance ?? '' }}
                                        </div>
                                        <div class="col-md-12 mt-1">
                                            <strong>Mariage:</strong> {{ $details->date_mariage ? \Carbon\Carbon::parse($details->date_mariage)->format('d/m/Y') : '' }}
                                            à {{ $details->lieu_mariage ?? '' }}
                                        </div>
                                    </div>

                                @elseif($item->typeActe->nom == 'décès' && $details)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Défunt:</strong> {{ $details->defunt_prenom ?? '' }} {{ $details->defunt_nom ?? '' }}<br>
                                            <strong>Né le:</strong> {{ $details->defunt_date_naissance ? \Carbon\Carbon::parse($details->defunt_date_naissance)->format('d/m/Y') : '' }}
                                            à {{ $details->defunt_lieu_naissance ?? '' }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Décès:</strong> {{ $details->date_deces ? \Carbon\Carbon::parse($details->date_deces)->format('d/m/Y') : '' }}
                                            à {{ $details->lieu_deces ?? '' }}<br>
                                            <strong>Cause:</strong> {{ $details->cause_deces ?? 'Non spécifiée' }}
                                        </div>
                                    </div>

                                @elseif($item->typeActe->nom == 'divorce' && $details)
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

                            <!-- Actions sur l'item (Super Admin) -->
                            @if($item->statut == 'en attente')
                                <div class="mt-2">
                                    <form action="{{ route('super-admin.demandes.traiter-item', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="action" value="accepter">
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Accepter cet acte ?')">
                                            <i class="bi bi-check-lg"></i> Accepter
                                        </button>
                                    </form>
                                    <form action="{{ route('super-admin.demandes.traiter-item', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="action" value="refuser">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Refuser cet acte ?')">
                                            <i class="bi bi-x-lg"></i> Refuser
                                        </button>
                                    </form>
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
                                $totalItems = $demande->items->count();
                                $acceptes = $demande->items->where('statut', 'acceptée')->count();
                                $refuses = $demande->items->where('statut', 'refusée')->count();
                                $enAttente = $demande->items->where('statut', 'en attente')->count();
                            @endphp
                            <span class="badge bg-secondary">{{ $totalItems }} total</span>
                            <span class="badge bg-success">{{ $acceptes }} acceptés</span>
                            <span class="badge bg-danger">{{ $refuses }} refusés</span>
                            <span class="badge bg-warning">{{ $enAttente }} en attente</span>

                            @if($demande->statut == 'partiellement_traitée')
                                <span class="badge bg-info ms-2">⚠️ Demande partiellement traitée</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>

                @if($demande->statut == 'en attente' || $demande->statut == 'partiellement_traitée')
                    <form action="{{ route('super-admin.demandes.traiter', $demande->id_demande) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="accepter">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Tout valider ?')">
                            <i class="bi bi-check-all"></i> Tout valider
                        </button>
                    </form>
                    <form action="{{ route('super-admin.demandes.traiter', $demande->id_demande) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="refuser">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Tout refuser ?')">
                            <i class="bi bi-x-circle"></i> Tout refuser
                        </button>
                    </form>
                @endif

                @if($demande->statut != 'en attente')
                    <form action="{{ route('super-admin.demandes.archiver', $demande->id_demande) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary" onclick="return confirm('Archiver cette demande ?')">
                            <i class="bi bi-archive"></i> Archiver
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique
    const ctx = document.getElementById('demandesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($labelsGraphique ?? []),
                datasets: [{
                    label: 'Demandes reçues',
                    data: @json($donneesGraphique ?? []),
                    backgroundColor: '#4f46e5',
                    borderRadius: 4,
                    barThickness: 14,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: '#f5f7f8' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Recherche et filtres
    const searchInput = document.getElementById('searchInput');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const rows = document.querySelectorAll('#demandesTable tbody tr[data-statut]');
    let currentFilter = 'tous';

    function applyFilters() {
        const search = searchInput.value.toLowerCase();
        rows.forEach(row => {
            const matchesStatut = currentFilter === 'tous' || row.dataset.statut === currentFilter;
            const matchesSearch = row.textContent.toLowerCase().includes(search);
            row.style.display = (matchesStatut && matchesSearch) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            filterBtns.forEach(b => b.classList.remove('active', 'btn-outline-secondary', 'btn-outline-warning', 'btn-outline-success', 'btn-outline-danger', 'btn-outline-info'));
            this.classList.add('active');
            this.classList.remove('btn-outline-secondary', 'btn-outline-warning', 'btn-outline-success', 'btn-outline-danger', 'btn-outline-info');
            this.classList.add('btn-' + this.dataset.filter === 'tous' ? 'secondary' : 'primary');
            currentFilter = this.dataset.filter;
            applyFilters();
        });
    });
</script>
@endpush