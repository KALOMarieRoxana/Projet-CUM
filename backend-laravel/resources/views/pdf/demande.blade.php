<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Demande {{ $demande->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
            color: #333; 
            padding: 20px;
        }
        
        /* ═══════════════════════════════════════════════════════════ */
        /* EN-TÊTE OFFICIEL - RÉPUBLIQUE DE MADAGASCAR                 */
        /* ═══════════════════════════════════════════════════════════ */
        .entete-officiel {
            width: 100%;
            padding-bottom: 12px;
            margin-bottom: 16px;
            border-bottom: 2px solid #1E40AF;
        }

        .entete-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* GAUCHE : logo + texte */
        .entete-gauche {
            width: 60%;
            vertical-align: middle;
            text-align: left;
        }

        .entete-gauche-inner {
        border-collapse: collapse;
        }

        .entete-logo-cell {
            width: 90px;
            vertical-align: middle;
            text-align: center;
            padding-right: 15px;
        }

        .entete-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .entete-texte-cell {
            vertical-align: middle;
            text-align: left;
            padding-left: 10px;
        }

        .entete-ligne1 {
            font-size: 18px;
            font-weight: bold;
            color: #1E3A8A;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
        }

        .entete-ligne2 {
            font-size: 15px;
            color: #1E3A8A;
            letter-spacing: 0.3px;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* SECTIONS                                                    */
        /* ═══════════════════════════════════════════════════════════ */
        .section {
            margin-bottom: 16px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            overflow: hidden;
        }
        .section-title {
            background: #EEF2FF;
            color: #4F46E5;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: bold;
            border-bottom: 1px solid #E5E7EB;
        }
        .section-content {
            padding: 12px 14px;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* TABLE INFOS                                                 */
        /* ═══════════════════════════════════════════════════════════ */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #F3F4F6;
            vertical-align: top;
            font-size: 11px;
        }
        .info-table td:first-child {
            font-weight: bold;
            color: #374151;
            width: 35%;
        }
        .info-table td:last-child {
            color: #111827;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* TABLEAU DES ACTES                                           */
        /* ═══════════════════════════════════════════════════════════ */
        .actes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .actes-table th {
            background: #F3F4F6;
            color: #374151;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #E5E7EB;
            font-size: 10px;
        }
        .actes-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #F3F4F6;
            vertical-align: top;
        }
        .actes-table tr:last-child td {
            border-bottom: none;
        }
        .actes-table .type-acte {
            font-weight: bold;
            color: #111827;
        }
        .actes-table .sous-type {
            color: #4F46E5;
            font-weight: 600;
        }
        .actes-table .no-sous-type {
            color: #9CA3AF;
            font-style: italic;
        }
        .actes-table .total-row {
            background: #EEF2FF;
            font-weight: bold;
            font-size: 12px;
        }
        .actes-table .total-row td {
            padding: 10px 6px;
            color: #4F46E5;
            border-top: 2px solid #4F46E5;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* BADGES                                                      */
        /* ═══════════════════════════════════════════════════════════ */
        .badge-langue {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-mg { background: #FEF3C7; color: #92400E; }
        .badge-fr { background: #DBEAFE; color: #1E40AF; }

        .badge-service {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-standard { background: #EEF2FF; color: #4F46E5; }
        .badge-express { background: #FEF3C7; color: #D97706; }

        /* ═══════════════════════════════════════════════════════════ */
        /* SIGNATURE EN BAS                                            */
        /* ═══════════════════════════════════════════════════════════ */
        .signature-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-left {
            width: 40%;
            vertical-align: bottom;
            font-size: 10px;
            color: #6B7280;
        }
        .signature-left .qr-placeholder {
            width: 80px;
            height: 80px;
            border: 1px dashed #9CA3AF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #9CA3AF;
            text-align: center;
            margin-top: 8px;
        }
        .signature-right {
            width: 60%;
            text-align: center;
            vertical-align: bottom;
            font-size: 11px;
            color: #374151;
        }
        .signature-right .label {
            font-weight: bold;
            margin-bottom: 4px;
        }
        .signature-right .date {
            font-size: 10px;
            color: #6B7280;
            margin-bottom: 8px;
        }
        .signature-box {
            display: inline-block;
            width: 180px;
            height: 80px;
            border: 2px dashed #9CA3AF;
            border-radius: 4px;
            text-align: center;
            line-height: 80px;
            font-size: 10px;
            color: #9CA3AF;
            font-style: italic;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* FOOTER                                                      */
        /* ═══════════════════════════════════════════════════════════ */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #9CA3AF;
            border-top: 1px solid #E5E7EB;
            padding: 8px;
            background: #FFF;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* TITRE + LIGNE RÉFÉRENCE/STATUT                              */
        /* ═══════════════════════════════════════════════════════════ */
        .titre-demande {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #1E3A8A;
            margin: 20px 0 12px 0;
            letter-spacing: 0.5px;
        }

        .ligne-reference-statut {
            width: 100%;
            margin-bottom: 20px;
            padding: 10px 14px;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
        }

        .ref-statut-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ref-cell {
            text-align: left;
            vertical-align: middle;
            font-size: 11px;
        }

        .statut-cell {
            text-align: right;
            vertical-align: middle;
            font-size: 11px;
        }

        .ref-label {
            font-weight: bold;
            color: #1E3A8A;
            margin-right: 4px;
        }

        .ref-value {
            color: #111827;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 10.5px;
        }

        .statut-label {
            font-weight: bold;
            color: #1E3A8A;
            margin-right: 6px;
        }

        .statut-value {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            color: #FFF;
        }

        .statut-acceptee { background: #10B981; }
        .statut-refusee  { background: #DC2626; }
        .statut-attente  { background: #F59E0B; }
    </style>
</head>
<body>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- EN-TÊTE OFFICIEL (logo Base64)                              -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="entete-officiel">
        <table class="entete-table">
            <tr>
                <td class="entete-logo-cell">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" alt="Logo" class="entete-logo">
                    @else
                        <div class="entete-logo" style="border: 1px dashed #ccc; text-align: center; line-height: 80px; font-size: 9px; color: #999;">
                            Logo
                        </div>
                    @endif
                </td>
                <td class="entete-texte-cell">
                    <div class="entete-ligne1">République de Madagascar</div>
                    <div class="entete-ligne2">État civil</div>
                </td>
            </tr>
        </table>
    </div>
    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- EN-TÊTE OFFICIEL (logo gauche + infos droite)               -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="entete-officiel">
        <table class="entete-table">
            <tr>
                <!-- GAUCHE : LOGO + TEXTE OFFICIEL -->
                <td class="entete-gauche">
                    <table class="entete-gauche-inner">
                        <tr>
                            <td class="entete-logo-cell">
                                @if(!empty($logoBase64))
                                    <img src="{{ $logoBase64 }}" alt="Logo" class="entete-logo">
                                @else
                                    <div class="entete-logo" style="border:1px dashed #ccc; text-align:center; line-height:80px; font-size:9px; color:#999;">
                                        Logo
                                    </div>
                                @endif
                            </td>
                            <td class="entete-texte-cell">
                                <div class="entete-ligne1">République de Madagascar</div>
                                <div class="entete-ligne2">État civil</div>
                            </td>
                        </tr>
                    </table>
                </td>

                <!-- DROITE : DATE + RÉFÉRENCE -->
                <td class="entete-droite">
                    <div class="entete-info">
                        <span class="entete-info-label">Date d'édition :</span>
                        <span class="entete-info-value">{{ $date_generation }}</span>
                    </div>
                    <div class="entete-info">
                        <span class="entete-info-label">Référence :</span>
                        <span class="entete-info-value">{{ $demande->reference }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- TITRE + LIGNE RÉFÉRENCE/STATUT                              -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="titre-demande">
        Demande d'acte d'état civil
    </div>

    <div class="ligne-reference-statut">
        <table class="ref-statut-table">
            <tr>
                <!-- GAUCHE : RÉFÉRENCE -->
                <td class="ref-cell">
                    <span class="ref-label">Référence :</span>
                    <span class="ref-value">{{ $demande->reference }}</span>
                </td>

                <!-- DROITE : STATUT -->
                <td class="statut-cell">
                    <span class="statut-label">Statut :</span>
                    @if($demande->statut === 'acceptée' || $demande->statut === 'acceptee')
                        <span class="statut-value statut-acceptee">✅ Acceptée</span>
                    @elseif($demande->statut === 'refusée' || $demande->statut === 'refusee')
                        <span class="statut-value statut-refusee">❌ Refusée</span>
                    @else
                        <span class="statut-value statut-attente">⏳ En attente</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- INFORMATIONS DU DEMANDEUR                                   -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-title">👤 INFORMATIONS DU DEMANDEUR</div>
        <div class="section-content">
            <table class="info-table">
                <tr>
                    <td>Nom :</td>
                    <td>{{ $demande->demandeur_nom }}</td>
                </tr>
                <tr>
                    <td>Prénom :</td>
                    <td>{{ $demande->demandeur_prenom }}</td>
                </tr>
                <tr>
                    <td>Adresse :</td>
                    <td>{{ $demande->demandeur_adresse }}</td>
                </tr>
                <tr>
                    <td>Contact :</td>
                    <td>{{ $demande->demandeur_contact }}</td>
                </tr>
                <tr>
                    <td>Relation :</td>
                    <td>{{ $demande->demandeur_relation ?? 'Non spécifiée' }}</td>
                </tr>
                <tr>
                    <td>Service :</td>
                    <td>
                        @if($demande->service === 'express')
                            <span class="badge-service badge-express">⚡ Express</span>
                        @else
                            <span class="badge-service badge-standard">🛡 Standard</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>


    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- LISTE DES ACTES DEMANDÉS AVEC SOUS-TYPES                    -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-title">📋 LISTE DES ACTES DEMANDÉS</div>
        <div class="section-content">
            <table class="actes-table">
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 18%;">Type d'acte</th>
                        <th style="width: 20%;">Sous-type</th>
                        <th style="width: 10%;">Langue</th>
                        <th style="width: 8%; text-align: center;">Qté</th>
                        <th style="width: 10%; text-align: center;">Service</th>
                        <th style="width: 15%; text-align: right;">Prix unitaire</th>
                        <th style="width: 15%; text-align: right;">Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $numero = 1;
                        $totalGeneral = 0;
                    @endphp

                    @forelse($actes as $acte)
                        @php
                            $typeActe = $acte->typeActe;
                            $supplement = $acte->supplement;
                            $langue = strtoupper($acte->langue ?? 'MG');
                            $prixActe = floatval($acte->prix_acte ?? 0);
                            $prixSupp = floatval($acte->prix_supplement ?? 0);
                            $qteActe = intval($acte->quantite ?? 1);
                            $qteSupp = intval($acte->quantite_supplement ?? 0);

                            $sousTotalActe = $prixActe * $qteActe;
                            $sousTotalSupp = ($supplement && $qteSupp > 0) ? ($prixSupp * $qteSupp) : 0;
                            $sousTotal = $sousTotalActe + $sousTotalSupp;

                            $totalGeneral += $sousTotal;

                            $serviceActe = $acte->type_service ?? $demande->service ?? 'standard';
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $numero++ }}</td>
                            <td class="type-acte">
                                {{ $typeActe->nom ?? 'Acte' }}
                            </td>
                            <td>
                                @if($supplement)
                                    <span class="sous-type">📋 {{ $supplement->nom }}</span>
                                @else
                                    <span class="no-sous-type">— Aucun —</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-langue {{ $langue === 'MG' ? 'badge-mg' : 'badge-fr' }}">
                                    {{ $langue === 'MG' ? '🇲🇬 MG' : '🇫🇷 FR' }}
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: bold;">
                                {{ $qteActe }}
                                @if($supplement && $qteSupp > 0)
                                    <br>
                                    <small style="color: #4F46E5;">+ {{ $qteSupp }} doc</small>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($serviceActe === 'express')
                                    <span class="badge-service badge-express">⚡</span>
                                @else
                                    <span class="badge-service badge-standard">🛡</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($supplement && $prixSupp > 0)
                                    {{ number_format($prixActe, 0, ',', ' ') }} Ar
                                    <br>
                                    <small style="color: #4F46E5;">+ {{ number_format($prixSupp, 0, ',', ' ') }} Ar</small>
                                @else
                                    {{ number_format($prixActe, 0, ',', ' ') }} Ar
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: bold; color: #4F46E5;">
                                {{ number_format($sousTotal, 0, ',', ' ') }} Ar
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #9CA3AF; padding: 20px;">
                                Aucun acte demandé
                            </td>
                        </tr>
                    @endforelse

                    <!-- Ligne TOTAL -->
                    @if(count($actes) > 0)
                    <tr class="total-row">
                        <td colspan="7" style="text-align: right;">
                            TOTAL GÉNÉRAL
                        </td>
                        <td style="text-align: right;">
                            {{ number_format($totalGeneral, 0, ',', ' ') }} Ar
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- SIGNATURE EN BAS                                            -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <!-- DROITE : SIGNATURE -->
                <td class="signature-right">
                    <div class="label">L'Officier d'État Civil</div>
                    <div class="date">
                        Fait à ........................., le {{ $date_generation }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- FOOTER                                                      -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="footer">
        Document généré le {{ $date_generation }} - Référence : {{ $demande->reference }}
        <br>
        Ce document est généré automatiquement. Toute reproduction non autorisée est interdite.
    </div>

</body>
</html>