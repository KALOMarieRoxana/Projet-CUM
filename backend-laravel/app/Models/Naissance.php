<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Naissance extends Model
{
    use HasFactory;

    protected $table = 'naissances';

    protected $fillable = [
        // Identité de la personne concernée
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'num_acte',
        'date_acte',

        // Informations des parents
        'nom_pere',
        'prenom_pere',
        'nom_mere',
        'prenom_mere',

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