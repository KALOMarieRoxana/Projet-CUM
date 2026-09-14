<?php

namespace App\Services;

use App\Models\Demande;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

// ✅ Utiliser BaconQrCode directement, pas simple-qrcode
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class DemandePdfService
{
    public function generer(Demande $demande): string
    {
        // Charger les relations
        $demande->load(['citoyen', 'demandeActes.typeActe', 'demandeActes.acte']);

        // ✅ Générer le QR code en SVG (sans Imagick ni GD)
        $qrContent = route('demandes.verifier', $demande->reference);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()   // ← Backend SVG pur, pas Imagick
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrContent);

        // Préparer les données pour la vue
        $data = [
            'demande'     => $demande,
            'actes'       => $demande->demandeActes,
            'qrCodeSvg'   => $qrCodeSvg,
            'dateEdition' => now()->format('d/m/Y H:i'),
        ];

        // Générer le PDF
        $pdf = Pdf::loadView('pdf.demande', $data)->setPaper('A4', 'portrait');

        // Sauvegarder
        $fileName = 'demandes/demande_' . $demande->reference . '_' . time() . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        // Mettre à jour la demande
        $demande->update([
            'pdf_path'         => $fileName,
            'pdf_genere_at'    => now(),
            'notification_lue' => false,
        ]);

        return $fileName;
    }
}