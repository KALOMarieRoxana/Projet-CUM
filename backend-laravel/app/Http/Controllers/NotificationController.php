<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    /**
     * Récupérer le citoyen authentifié (robuste)
     */
    private function getCitoyen(Request $request)
    {
        // Essayer plusieurs guards
        $citoyen = Auth::guard('citoyen')->user();
        
        if (!$citoyen) {
            $citoyen = $request->user();
        }
        
        if (!$citoyen && Auth::check()) {
            $citoyen = Auth::user();
        }
        
        return $citoyen;
    }

    /**
     * Récupérer l'ID du citoyen
     */
    private function getCitoyenId($citoyen)
    {
        return $citoyen->id_citoyens ?? $citoyen->id ?? null;
    }

    /**
     * Compteur de notifications non lues
     */
    public function compteur(Request $request)
    {
        $citoyen = $this->getCitoyen($request);
        
        if (!$citoyen) {
            return response()->json(['success' => true, 'count' => 0]);
        }

        $citoyenId = $this->getCitoyenId($citoyen);

        $count = Demande::where('citoyen_id', $citoyenId)
            ->whereIn('statut', ['acceptée', 'refusée', 'partiellement_acceptée'])
            ->where('notification_lue', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Liste des notifications
     */
    public function index(Request $request)
    {
        $citoyen = $this->getCitoyen($request);
        
        if (!$citoyen) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
                'notifications' => [],
                'non_lues' => 0,
            ], 401);
        }

        $citoyenId = $this->getCitoyenId($citoyen);

        $notifications = Demande::where('citoyen_id', $citoyenId)
            ->whereIn('statut', ['acceptée', 'refusée', 'partiellement_acceptée'])
            ->orderBy('date_traitement', 'desc')
            ->take(30)
            ->get()
            ->map(function ($demande) {
                return [
                    'id' => $demande->id_demande,
                    'reference' => $demande->reference,
                    'statut' => $demande->statut,
                    'message' => $this->getMessage($demande),
                    'prix_total' => $demande->prix_total,
                    'nombre_actes' => $demande->nombre_actes,
                    'pdf_path' => $demande->pdf_path,
                    'a_pdf' => !empty($demande->pdf_path),
                    'lue' => (bool) $demande->notification_lue,
                    'date' => $demande->date_traitement?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'non_lues' => $notifications->where('lue', false)->count(),
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function marquerLue(Request $request, $id)
    {
        $citoyen = $this->getCitoyen($request);
        
        if (!$citoyen) {
            return response()->json(['success' => false], 401);
        }

        $citoyenId = $this->getCitoyenId($citoyen);

        $demande = Demande::where('citoyen_id', $citoyenId)
            ->where('id_demande', $id)
            ->first();

        if ($demande) {
            $demande->update(['notification_lue' => true]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function marquerToutesLues(Request $request)
    {
        $citoyen = $this->getCitoyen($request);
        
        if (!$citoyen) {
            return response()->json(['success' => false], 401);
        }

        $citoyenId = $this->getCitoyenId($citoyen);

        Demande::where('citoyen_id', $citoyenId)
            ->where('notification_lue', false)
            ->update(['notification_lue' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Télécharger le PDF
     */
    public function telechargerPdf(Request $request, $id)
    {
        $citoyen = $this->getCitoyen($request);
        
        if (!$citoyen) {
            return response()->json(['success' => false], 401);
        }

        $citoyenId = $this->getCitoyenId($citoyen);

        $demande = Demande::where('citoyen_id', $citoyenId)
            ->where('id_demande', $id)
            ->first();

        if (!$demande) {
            return response()->json([
                'success' => false,
                'message' => 'Demande non trouvée.'
            ], 404);
        }

        if (!$demande->pdf_path || !Storage::disk('public')->exists($demande->pdf_path)) {
            return response()->json([
                'success' => false,
                'message' => 'PDF non disponible.'
            ], 404);
        }

        return Storage::disk('public')->download(
            $demande->pdf_path,
            'Demande-' . $demande->reference . '.pdf'
        );
    }

    private function getMessage($demande)
    {
        switch ($demande->statut) {
            case 'acceptée':
                return "✅ Votre demande N°{$demande->reference} a été acceptée. Vous pouvez télécharger votre document.";
            case 'refusée':
                return "❌ Votre demande N°{$demande->reference} a été refusée.";
            case 'partiellement_acceptée':
                return "⚠️ Votre demande N°{$demande->reference} a été partiellement acceptée.";
            default:
                return "Le statut de votre demande N°{$demande->reference} a changé.";
        }
    }
}