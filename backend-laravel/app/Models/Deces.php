<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Deces extends Model
{
    use HasFactory;

    protected $table = 'deces';

    protected $fillable = [
        // Informations du défunt
        'nom_defunt',
        'prenom_defunt',
        'date_naissance_defunt',
        'date_deces',
        'lieu_deces',
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