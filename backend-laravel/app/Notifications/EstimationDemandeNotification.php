<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EstimationDemandeNotification extends Notification
{
    use Queueable;

    public $demande;

    public function __construct(Demande $demande)
    {
        $this->demande = $demande;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $isExpress = $this->demande->service === 'express';

        return [
            'type' => 'estimation',
            'demande_id' => $this->demande->id_demande,
            'reference' => $this->demande->reference,
            'service' => $this->demande->service,
            'message' => $isExpress
                ? "⚡ Service Express : Votre demande #{$this->demande->reference} est en cours de traitement (max 24h)."
                : "🛡 Service Standard : Votre demande #{$this->demande->reference} est en cours de traitement (max 72h).",
            'date' => now()->toIso8601String(),
        ];
    }
}