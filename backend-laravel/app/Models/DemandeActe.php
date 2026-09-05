<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DemandeActe extends Model
{
    protected $table = 'demande_actes';

    protected $fillable = [
        'demande_id',
        'type_acte', // Utilisation de la clé textuelle
        'acte_type',
        'acte_id',
        'prix_unitaire',
        'quantite',
        'sous_total',
        'statut',
        'commentaire',
        'date_traitement',
    ];

    /**
     * Relation Polymorphique vers Naissance, Mariage, Deces ou Divorce
     */
    public function acte(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation vers la demande globale
     */
    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class, 'demande_id', 'id_demande');
    }

    /**
     * Relation vers le type d'acte (Catalogue) via le champ 'type_acte'
     */
    public function typeActeRelation(): BelongsTo
    {
        // $this->belongsTo(TypeActe::class, 'clé_étrangère_locale', 'clé_primaire_distante');
        return $this->belongsTo(TypeActe::class, 'type_acte', 'type_acte');
    }
}