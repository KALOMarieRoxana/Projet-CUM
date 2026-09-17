@php
    $points = $points ?? [5, 10, 8, 12, 15, 13, 18, 20, 19, 22];
    $max = max($points);
    $min = min($points);
    $largeur = 100;
    $hauteur = 30;
    $nbPoints = count($points);

    $coords = [];
    foreach ($points as $i => $p) {
        $x = ($i / ($nbPoints - 1)) * $largeur;
        $y = $hauteur - (($p - $min) / max($max - $min, 1)) * $hauteur;
        $coords[] = round($x, 2) . ',' . round($y, 2);
    }
    $chemin = implode(' L', $coords);
    $estPositif = ($pourcentage ?? 0) >= 0;
@endphp

<div class="stat-chart-card">
    <div class="stat-chart-header">
        <span class="stat-chart-label">{{ $label }}</span>
        <div class="stat-chart-icon" style="background: {{ $bgCouleur }}; color: {{ $couleur }};">
            <i class="bi {{ $icon }}"></i>
        </div>
    </div>

    <div class="stat-chart-value">{{ $valeur }}</div>

    <div class="stat-chart-evolution" style="color: {{ $estPositif ? '#059669' : '#DC2626' }};">
        <i class="bi bi-{{ $estPositif ? 'arrow-up-right' : 'arrow-down-right' }}"></i>
        {{ $estPositif ? '+' : '' }}{{ $pourcentage }}%
    </div>

    <div class="stat-chart-graph">
        <svg viewBox="0 0 {{ $largeur }} {{ $hauteur }}" preserveAspectRatio="none" style="width: 100%; height: 40px;">
            <defs>
                <linearGradient id="grad-{{ md5($label) }}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="{{ $couleur }}" stop-opacity="0.25"/>
                    <stop offset="100%" stop-color="{{ $couleur }}" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <path d="M0,{{ $hauteur }} L{{ $chemin }} L{{ $largeur }},{{ $hauteur }} Z"
                  fill="url(#grad-{{ md5($label) }})"/>
            <path d="M{{ $chemin }}"
                  fill="none"
                  stroke="{{ $couleur }}"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"/>
        </svg>
    </div>
</div>