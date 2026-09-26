<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationEmailCitoyen extends Mailable
{
    use Queueable, SerializesModels;

    public $citoyen;
    public $lienVerification;

    public function __construct($citoyen, $lienVerification)
    {
        $this->citoyen = $citoyen;
        $this->lienVerification = $lienVerification;
    }

    public function build()
    {
        return $this->subject('Confirmez votre adresse email')
            ->view('emails.verification-citoyen');
    }
}