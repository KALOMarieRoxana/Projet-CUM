<?php

namespace App\Console\Commands;

use App\Models\Demande;
use App\Notifications\EstimationDemandeNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TraiterEstimations extends Command
{
    protected $signature = 'demandes:traiter-estimations';
    protected $description = 'Traite les estimations arrivées à échéance';

    public function handle()
    {
        $this->info('🔍 Recherche des estimations...');

        $demandes = Demande::where('statut', 'en_attente')
            ->where('estimation_envoyee', false)
            ->whereNotNull('date_estimation')
            ->where('date_estimation', '<=', now())
            ->get();

        if ($demandes->isEmpty()) {
            $this->info('✅ Aucune estimation à traiter.');
            return;
        }

        foreach ($demandes as $demande) {
            $demande->update(['estimation_envoyee' => true]);
            
            $citoyen = $demande->citoyen;
            if ($citoyen) {
                $citoyen->notify(new EstimationDemandeNotification($demande));
            }

            Log::info('📤 Estimation envoyée:', [
                'reference' => $demande->reference,
                'service' => $demande->service,
            ]);
        }

        $this->info("✅ {$demandes->count()} estimations traitées.");
    }
}