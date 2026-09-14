<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande {{ $demande->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1F2937; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #4F46E5; padding-bottom: 15px; margin-bottom: 20px; }
        .header-left h1 { font-size: 20px; color: #4F46E5; margin-bottom: 4px; }
        .header-left p { font-size: 11px; color: #6B7280; }
        .header-right { text-align: right; }
        .header-right p { font-size: 11px; color: #6B7280; }
        .ref-box { background: #EEF2FF; border-left: 4px solid #4F46E5; padding: 12px; margin-bottom: 20px; }
        .ref-box h2 { font-size: 16px; color: #4F46E5; }
        .ref-box p { font-size: 11px; color: #6B7280; margin-top: 4px; }
        .section { margin-bottom: 20px; }
        .section h3 { font-size: 13px; color: #4F46E5; border-bottom: 1px solid #E5E7EB; padding-bottom: 6px; margin-bottom: 10px; text-transform: uppercase; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .info-item { padding: 6px 0; border-bottom: 1px dashed #F3F4F6; }
        .info-item strong { color: #374151; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background: #F3F4F6; padding: 8px; text-align: left; font-size: 11px; color: #374151; border: 1px solid #E5E7EB; }
        table td { padding: 8px; border: 1px solid #E5E7EB; font-size: 11px; }
        table .text-right { text-align: right; }
        table .text-center { text-align: center; }
        .total-row { background: #EEF2FF; font-weight: bold; font-size: 13px; }
        .total-row td { color: #4F46E5; }
        .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid #E5E7EB; }
        .qr-code { text-align: center; }
        .qr-code svg { width: 100px; height: 100px; }
        .qr-code p { font-size: 9px; color: #6B7280; margin-top: 4px; }
        .signature { text-align: center; font-size: 10px; color: #6B7280; }
        .signature .line { width: 150px; border-top: 1px solid #374151; margin-top: 40px; padding-top: 4px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #D1FAE5; color: #065F46; }
        .badge-warning { background: #FEF3C7; color: #92400E; }
        .badge-danger { background: #FEE2E2; color: #991B1B; }
    </style>
</head>
<body>

    {{-- En-tête --}}
    <div class="header">
        <div class="header-left">
            <h1>PORTAIL CITOYEN</h1>
            <p>Service de l'État Civil</p>
        </div>
        <div class="header-right">
            <p><strong>Date d'édition :</strong> {{ $dateEdition }}</p>
            <p><strong>Référence :</strong> {{ $demande->reference }}</p>
        </div>
    </div>

    {{-- Référence --}}
    <div class="ref-box">
        <h2>📌 Demande d'acte d'état civil</h2>
        <p>Référence : <strong>{{ $demande->reference }}</strong> | Statut :
            @if($demande->statut === 'acceptée')
                <span class="badge badge-success">✅ Acceptée</span>
            @elseif($demande->statut === 'refusée')
                <span class="badge badge-danger">❌ Refusée</span>
            @else
                <span class="badge badge-warning">⏳ {{ ucfirst($demande->statut) }}</span>
            @endif
        </p>
    </div>

    {{-- Actes --}}
    <div class="section">
        <h3>Liste des actes demandés</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type d'acte</th>
                    <th class="text-center">Langue</th>
                    <th class="text-center">Quantité</th>
                    <th class="text-center">Service</th>
                    <th class="text-right">Prix unitaire</th>
                    <th class="text-right">Sous-total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($actes as $index => $acte)
                    @php
                        $typeNom = $acte->typeActe->nom ?? 'Acte';
                        $langue = strtoupper($acte->langue ?? 'MG');
                        $serviceActe = $acte->type_service ?? 'standard';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $typeNom }}</td>
                        <td class="text-center">{{ $langue === 'MG' ? 'Malgache' : 'Français' }}</td>
                        <td class="text-center">{{ $acte->quantite }}</td>
                        <td class="text-center">{{ ucfirst($serviceActe) }}</td>
                        <td class="text-right">{{ number_format($acte->prix_unitaire, 0, ',', ' ') }} Ar</td>
                        <td class="text-right">{{ number_format($acte->sous_total, 0, ',', ' ') }} Ar</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="6" class="text-right">TOTAL GÉNÉRAL</td>
                    <td class="text-right">{{ number_format($demande->prix_total, 0, ',', ' ') }} Ar</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pied de page avec QR code --}}
    <div class="footer">
        <div class="qr-code">
            {{-- ✅ SVG direct, sans img/base64 --}}
            {!! $qrCodeSvg !!}
            <p>Scannez pour vérifier</p>
        </div>
        <div class="signature">
            <p>Cachet et signature</p>
            <div class="line"></div>
        </div>
    </div>

</body>
</html>