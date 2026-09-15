<?php

namespace App\Services;

use App\Models\Demande;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public function genererPdf(Demande $demande)
    {
        $actes = $demande->demandeActes()->with(['acte', 'supplement', 'typeActe'])->get();

        $data = [
            'demande'         => $demande,
            'citoyen'         => $demande->citoyen,
            'actes'           => $actes,
            'date_generation' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.demande', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'demandes/demande_' . $demande->reference . '_' . time() . '.pdf';

        Storage::disk('public')->put($filename, $pdf->output());

        $demande->update([
            'pdf_path'         => $filename,
            'pdf_genere_at'    => now(),
            'notification_lue' => false,
        ]);

        return $filename;
    }
}