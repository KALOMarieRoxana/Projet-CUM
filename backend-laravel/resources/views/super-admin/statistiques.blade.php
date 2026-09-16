@extends('layouts.admin')

@section('title', 'Statistiques')

@section('content')

{{-- ═══════════ HEADER ═══════════ --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Statistiques</h4>
        <small class="text-muted">Vue d'ensemble des demandes</small>
    </div>
    <nav>
        <span class="text-muted">Maison</span> &gt;
        <span>Super Admin</span> &gt;
        <span>Statistiques</span>
    </nav>
</div>

{{-- ═══════════ 4 CARTES ═══════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef2ff;color:#4f46e5;">
                <i class="bi bi-ticket-perforated fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted small mb-0">Total des demandes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff7ed;color:#ea580c;">
                <i class="bi bi-hourglass-split fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">{{ $stats['en_attente'] }}</h3>
                <p class="text-muted small mb-0">En attente</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;color:#059669;">
                <i class="bi bi-check-circle fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">{{ $stats['acceptee'] }}</h3>
                <p class="text-muted small mb-0">Acceptées</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                <i class="bi bi-x-circle fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">{{ $stats['refusee'] }}</h3>
                <p class="text-muted small mb-0">Refusées</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ DONUT PAR STATUT ═══════════ --}}
<div class="content-card p-4">
    <h5 class="fw-bold mb-1">Classement par statut</h5>
    <p class="text-muted small mb-4">Répartition des demandes par statut</p>

    <div class="d-flex align-items-center gap-5 flex-wrap">
        <div style="position: relative; width: 200px; height: 200px; flex-shrink: 0;">
            <svg width="200" height="200" viewBox="0 0 200 200">
                <circle cx="100" cy="100" r="70" fill="none"
                        stroke="#F3F4F6" stroke-width="30" />

                @php
                    $total  = $stats['total'] ?: 1;
                    $rayon  = 70;
                    $circ   = 2 * M_PI * $rayon;
                    $offset = 0;
                @endphp

                @foreach($parStatut as $seg)
                    @php
                        $ratio = $seg['value'] / $total;
                        $dash  = $ratio * $circ;
                        $gap   = $circ - $dash;
                    @endphp
                    @if($seg['value'] > 0)
                        <circle cx="100" cy="100" r="{{ $rayon }}"
                                fill="none"
                                stroke="{{ $seg['color'] }}"
                                stroke-width="30"
                                stroke-dasharray="{{ $dash }} {{ $gap }}"
                                stroke-dashoffset="{{ -$offset }}"
                                transform="rotate(-90 100 100)" />
                        @php $offset += $dash; @endphp
                    @endif
                @endforeach
            </svg>

            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                <div style="font-size: 32px; font-weight: 800; color: #111827; line-height: 1;">
                    {{ $stats['total'] }}
                </div>
                <div style="font-size: 11px; color: #6B7280; margin-top: 2px;">
                    Demandes
                </div>
            </div>
        </div>

        <div style="flex: 1; min-width: 250px;">
            @foreach($parStatut as $seg)
                @php
                    $pct = $stats['total'] > 0 ? round(($seg['value'] / $stats['total']) * 100) : 0;
                @endphp
                <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; border-radius: 50%; background: {{ $seg['color'] }};"></span>
                        <span class="fw-semibold">{{ $seg['label'] }}</span>
                        <span class="text-muted small">({{ $seg['value'] }})</span>
                    </div>
                    <span class="fw-bold">{{ $pct }} %</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════ ANALYTIQUE ═══════════ --}}
<div class="content-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1">Analytique</h5>
            <p class="text-muted small mb-0" id="periodeLabel">Analyse des demandes sur les 12 derniers mois</p>
        </div>

        {{-- ✅ Onglets fonctionnels --}}
        <div class="btn-group btn-group-sm" role="group" id="periodeBtns">
            <button type="button" class="btn btn-light active" data-periode="12mois">12 mois</button>
            <button type="button" class="btn btn-light" data-periode="30jours">30 jours</button>
            <button type="button" class="btn btn-light" data-periode="7jours">7 jours</button>
            <button type="button" class="btn btn-light" data-periode="24heures">24 heures</button>
        </div>
    </div>

    <canvas id="chartMois" height="90"></canvas>

    <div class="row text-center mt-4 pt-3 border-top">
        <div class="col-3">
            <div class="fw-bold" style="font-size: 22px; color:#4F46E5;">{{ $stats['total'] }}</div>
            <div class="text-muted small">Total</div>
        </div>
        <div class="col-3">
            <div class="fw-bold" style="font-size: 22px; color:#10B981;">{{ $stats['acceptee'] }}</div>
            <div class="text-muted small">Acceptées</div>
        </div>
        <div class="col-3">
            <div class="fw-bold" style="font-size: 22px; color:#F59E0B;">{{ $stats['en_attente'] }}</div>
            <div class="text-muted small">En attente</div>
        </div>
        <div class="col-3">
            <div class="fw-bold" style="font-size: 22px; color:#EF4444;">{{ $stats['refusee'] }}</div>
            <div class="text-muted small">Refusées</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ✅ Données des 4 périodes
    const dataPeriodes = {
        '12mois':   @json($parMois),
        '30jours':  @json($par30Jours),
        '7jours':   @json($par7Jours),
        '24heures': @json($par24Heures),
    };

    // ✅ Labels associés
    const labelPeriode = {
        '12mois':   'Analyse des demandes sur les 12 derniers mois',
        '30jours':  'Analyse des demandes sur les 30 derniers jours',
        '7jours':   'Analyse des demandes sur les 7 derniers jours',
        '24heures': 'Analyse des demandes sur les 24 dernières heures',
    };

    const ctx = document.getElementById('chartMois');
    if (!ctx) return;

    // ✅ Initialisation du graphique (12 mois par défaut)
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dataPeriodes['12mois'].map(d => d.label),
            datasets: [{
                label: 'Demandes',
                data: dataPeriodes['12mois'].map(d => d.total),
                backgroundColor: '#6366F1',
                borderRadius: 6,
                barThickness: 28,
                maxBarThickness: 40,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#9CA3AF' },
                    grid: { color: '#F3F4F6', drawBorder: false }
                },
                x: {
                    ticks: { color: '#9CA3AF', font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });

    // ✅ Gestion des onglets
    const btnPeriode = document.querySelectorAll('#periodeBtns button');
    const periodeLabel = document.getElementById('periodeLabel');

    btnPeriode.forEach(btn => {
        btn.addEventListener('click', function () {
            const periode = this.dataset.periode;
            const data = dataPeriodes[periode];

            // Mise à jour du graphique
            chart.data.labels = data.map(d => d.label);
            chart.data.datasets[0].data = data.map(d => d.total);
            chart.update();

            // Mise à jour du texte
            periodeLabel.textContent = labelPeriode[periode];

            // Mise à jour de l'onglet actif
            btnPeriode.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>
@endpush