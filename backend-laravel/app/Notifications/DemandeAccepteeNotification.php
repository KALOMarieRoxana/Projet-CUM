<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DemandeAccepteeNotification extends Notification
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
            'type'         => 'demande_acceptee',
            'demande_id'   => $this->demande->id_demande,
            'reference'    => $this->demande->reference,
            'message'      => "Votre demande #{$this->demande->reference} a été acceptée ! Votre document est prêt à être téléchargé.",
            'pdf_path'     => $this->demande->pdf_path,
            'pdf_url'      => $this->demande->pdf_path 
                ? url('storage/' . $this->demande->pdf_path)
                : null,
            'prix_total'   => $this->demande->prix_total,
            'date'         => now()->toIso8601String(),
        ];
    }
}