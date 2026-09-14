<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Services\DemandePdfService;
use Illuminate\Support\Facades\Storage;

class DemandePdfController extends Controller
{
    protected $pdfService;

    public function __construct(DemandePdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Télécharger le PDF d'une demande
     */
    public function telecharger($id)
    {
        $demande = Demande::findOrFail($id);
        $user = auth()->user();

        $isOwner = $demande->citoyen_id == ($user->id_citoyens ?? $user->id);
        $isAdmin = in_array($user->role ?? '', ['admin', 'super_admin', 'agent']);

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Accès non autorisé.');
        }

        // Si le PDF n'existe pas, on le génère
        if (!$demande->pdf_path || !Storage::disk('public')->exists($demande->pdf_path)) {
            $this->pdfService->generer($demande);
            $demande->refresh();
        }

        // Marquer comme lue
        $demande->update(['notification_lue' => true]);

        $path = storage_path('app/public/' . $demande->pdf_path);

        return response()->download($path, 'demande_' . $demande->reference . '.pdf');
    }

    /**
     * Vérifier une demande via QR code
     */
    public function verifier($reference)
    {
        $demande = Demande::where('reference', $reference)->first();

        if (!$demande) {
            return view('demandes.verification', ['valide' => false, 'reference' => $reference]);
        }

        return view('demandes.verification', [
            'valide'    => true,
            'demande'   => $demande,
            'reference' => $reference,
        ]);
    }
}