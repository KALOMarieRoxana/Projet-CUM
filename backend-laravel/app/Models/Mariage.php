<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Mariage extends Model
{
    use HasFactory;

    protected $table = 'mariages';

    protected $fillable = [
        // Informations Époux
        'nom_epoux',
        'prenom_epoux',
        'date_naissance_epoux',
        'lieu_naissance_epoux',

        // Informations Épouse
        'nom_epouse',
        'prenom_epouse',
        'date_naissance_epouse',
        'lieu_naissance_epouse',

        // Informations sur le Mariage
        'date_mariage',
        'lieu_mariage',
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