<?php

namespace App\Http\Controllers;

use App\Models\NotificationAdmin;
use Illuminate\Http\Request;

class NotificationAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * COMPTEUR (polling toutes les 15s)
     * ═══════════════════════════════════════════════════════════
     */
    public function compteur()
    {
        $count = NotificationAdmin::where('lue', false)->count();

        return response()->json(['count' => $count]);
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * LISTE DES 20 DERNIÈRES NOTIFICATIONS
     * ═══════════════════════════════════════════════════════════
     */
    public function index()
    {
        $notifications = NotificationAdmin::orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $nonLues = NotificationAdmin::where('lue', false)->count();

        return response()->json([
            'notifications' => $notifications,
            'non_lues'      => $nonLues,
        ]);
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * MARQUER UNE COMME LUE
     * ═══════════════════════════════════════════════════════════
     */
    public function marquerLue($id)
    {
        $notif = NotificationAdmin::findOrFail($id);
        $notif->update(['lue' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * TOUT MARQUER COMME LU
     * ═══════════════════════════════════════════════════════════
     */
    public function marquerToutesLues()
    {
        NotificationAdmin::where('lue', false)->update(['lue' => true]);

        return response()->json(['success' => true]);
    }
}