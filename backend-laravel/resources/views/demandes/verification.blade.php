 <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification de demande</title>
    <style>
        body { font-family: Arial, sans-serif; background: #F3F4F6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #FFF; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); max-width: 500px; width: 100%; text-align: center; }
        .icon { font-size: 60px; margin-bottom: 16px; }
        .success { color: #10B981; }
        .error { color: #EF4444; }
        h1 { font-size: 22px; margin: 0 0 8px 0; }
        p { color: #6B7280; font-size: 14px; margin: 4px 0; }
        .details { text-align: left; margin-top: 24px; padding: 16px; background: #F9FAFB; border-radius: 8px; font-size: 13px; }
        .details div { margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="card">
        @if($valide)
            <div class="icon success">✅</div>
            <h1 class="success">Demande valide</h1>
            <p>Référence : <strong>{{ $reference }}</strong></p>

            <div class="details">
                <div><strong>Demandeur :</strong> {{ $demande->demandeur_prenom }} {{ $demande->demandeur_nom }}</div>
                <div><strong>Statut :</strong> {{ ucfirst($demande->statut) }}</div>
                <div><strong>Date :</strong> {{ $demande->created_at->format('d/m/Y H:i') }}</div>
                <div><strong>Prix total :</strong> {{ number_format($demande->prix_total, 0, ',', ' ') }} Ar</div>
            </div>
        @else
            <div class="icon error">❌</div>
            <h1 class="error">Demande invalide</h1>
            <p>Référence : <strong>{{ $reference }}</strong></p>
            <p>Aucune demande ne correspond à cette référence.</p>
        @endif
    </div>
</body>
</html>