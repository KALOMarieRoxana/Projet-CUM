<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DemandeRefuseeNotification extends Notification
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
        return [
            'type'         => 'demande_refusee',
            'demande_id'   => $this->demande->id_demande,
            'reference'    => $this->demande->reference,
            'message'      => "❌ Votre demande #{$this->demande->reference} a été refusée.",
            'commentaire'  => $this->demande->commentaire_admin,
            'date'         => now()->toIso8601String(),
        ];
    }
}