<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Divorce extends Model
{
    use HasFactory;

    protected $table = 'divorces';

    protected $fillable = [
        // Informations des ex-époux
        'nom_epoux',
        'prenom_epoux',
        'nom_epouse',
        'prenom_epouse',

        // Informations sur le jugement/transcription
        'date_jugement',
        'num_jugement',
        'tribunal',
        'num_acte',

        // Options, langues et frais
        'langue',
        'type_service',
        'sigle',
        'nbre_com',
        'montantStandardMG',
        'montantExpressMG',
        'montantStandardFR',
        'montantExpressFR',
    ];

    /**
     * Relation polymorphique vers demande_actes
     */
    public function demandeActe(): MorphOne
    {
        return $this->morphOne(DemandeActe::class, 'acte');
    }
}