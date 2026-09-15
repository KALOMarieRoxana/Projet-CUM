<?php

namespace App\Services;

use App\Models\Demande;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfService
{
    public function genererPdf(Demande $demande)
    {
        // 1️⃣ Charger les actes avec leurs relations
        $actes = $demande->demandeActes()
            ->with(['acte', 'supplement', 'typeActe'])
            ->get();

        // 2️⃣ Préparer le logo en Base64 ✅
        $logoBase64 = $this->getLogoBase64();

        // 3️⃣ Données envoyées à la vue
        $data = [
            'demande'         => $demande,
            'citoyen'         => $demande->citoyen,
            'actes'           => $actes,
            'date_generation' => now()->format('d/m/Y H:i'),
            'logoBase64'      => $logoBase64, // ✅ ajouté
        ];

        // 4️⃣ Générer le PDF
        $pdf = Pdf::loadView('pdf.demande', $data);
        $pdf->setPaper('A4', 'portrait');

        // 5️⃣ Sauvegarder le fichier
        $filename = 'demandes/demande_' . $demande->reference . '_' . time() . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        // 6️⃣ Mettre à jour la demande
        $demande->update([
            'pdf_path'         => $filename,
            'pdf_genere_at'    => now(),
            'notification_lue' => false,
        ]);

        return $filename;
    }

    /**
     * Convertit le logo en Base64 pour DomPDF.
     * Essaie plusieurs emplacements possibles.
     */
    private function getLogoBase64(): ?string
    {
        // Liste des chemins possibles (par ordre de priorité)
        $cheminsPossibles = [
            public_path('image/logo.png'),                      // public/image/logo.png
            public_path('images/logo.png'),                     // public/images/logo.png
            storage_path('app/public/image/logo.png'),          // storage/app/public/image/logo.png
            storage_path('app/public/images/logo.png'),         // storage/app/public/images/logo.png
            base_path('public/image/logo.png'),                 // fallback
        ];

        foreach ($cheminsPossibles as $path) {
            if (file_exists($path)) {
                $mime = mime_content_type($path) ?: 'image/png';
                $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));

                Log::info('✅ Logo chargé en Base64', [
                    'path' => $path,
                    'mime' => $mime,
                    'taille' => strlen($base64) . ' caractères',
                ]);

                return $base64;
            }
        }

        // Aucun logo trouvé
        Log::error('❌ Logo introuvable. Chemins testés :', $cheminsPossibles);

        return null;
    }
}