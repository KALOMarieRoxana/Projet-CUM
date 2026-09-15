<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Demande {{ $demande->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #4F46E5; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #4F46E5; margin: 0; font-size: 22px; }
        .header p { margin: 5px 0; color: #666; }
        .section { margin-bottom: 20px; }
        .section h3 { background: #EEF2FF; color: #4F46E5; padding: 8px; margin: 0 0 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        table td, table th { padding: 6px; border-bottom: 1px solid #E5E7EB; text-align: left; }
        table td:first-child { font-weight: bold; width: 40%; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #E5E7EB; padding-top: 5px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; background: #D1FAE5; color: #065F46; }
    </style>
</head>
<body>

    <div class="header">
        <h1>RÉPUBLIQUE DE MADAGASCAR</h1>
        <p>Ministère de l'Intérieur - Service de l'État Civil</p>
        <p><strong>Référence : {{ $demande->reference }}</strong></p>
    </div>

    <div class="section">
        <h3>INFORMATIONS DU DEMANDEUR</h3>
        <table>
            <tr><td>Nom et Prénom</td><td>{{ $demande->demandeur_nom }} {{ $demande->demandeur_prenom }}</td></tr>
            <tr><td>Adresse</td><td>{{ $demande->demandeur_adresse }}</td></tr>
            <tr><td>Contact</td><td>{{ $demande->demandeur_contact }}</td></tr>
            <tr><td>Relation</td><td>{{ $demande->demandeur_relation ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>PERSONNE CONCERNÉE</h3>
        <table>
            <tr><td>Nom et Prénom</td><td>{{ $demande->personne_nom }} {{ $demande->personne_prenom }}</td></tr>
            <tr><td>Date de naissance</td><td>{{ $demande->personne_date_naissance }}</td></tr>
            <tr><td>Lieu de naissance</td><td>{{ $demande->personne_lieu_naissance }}</td></tr>
            <tr><td>Numéro d'acte</td><td>{{ $demande->personne_numero_acte ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>DÉTAILS DE LA DEMANDE</h3>
        <table>
            <tr><td>Service</td><td>{{ ucfirst($demande->service) }}</td></tr>
            <tr><td>Prix total</td><td>{{ number_format($demande->prix_total, 0, ',', ' ') }} Ar</td></tr>
            <tr><td>Nombre d'actes</td><td>{{ $demande->nombre_actes }}</td></tr>
            <tr><td>Statut</td><td><span class="badge">ACCEPTÉE</span></td></tr>
            <tr><td>Date de traitement</td><td>{{ $demande->date_traitement ? $demande->date_traitement->format('d/m/Y H:i') : '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>ACTES DEMANDÉS</h3>
        <table>
            <thead>
                <tr><th>Type</th><th>Document</th><th>Qté</th><th>Prix</th></tr>
            </thead>
            <tbody>
                @foreach($actes as $acte)
                    <tr>
                        <td>{{ $acte->typeActe->nom ?? '-' }}</td>
                        <td>{{ $acte->supplement->nom ?? 'Aucun' }}</td>
                        <td>{{ $acte->quantite }}</td>
                        <td>{{ number_format($acte->sous_total, 0, ',', ' ') }} Ar</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Document généré le {{ $date_generation }} - Référence : {{ $demande->reference }}
    </div>

</body>
</html>