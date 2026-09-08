<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DemandeActe extends Model
{
    use HasFactory;

    protected $table = 'demande_actes';

    protected $fillable = [
        'demande_id',
        'type_acte', // Ex: 'naissance', 'mariage', 'deces', 'divorce'
        'acte_type', // Pour la relation polymorphique (ex: App\Models\Naissance)
        'acte_id',   // ID de l'acte concerné
        'prix_unitaire',
        'quantite',
        'sous_total',
        'statut',
        'commentaire',
        'date_traitement',
    ];

    protected $casts = [
        'prix_unitaire'   => 'decimal:2',
        'sous_total'      => 'decimal:2',
        'quantite'        => 'integer',
        'date_traitement' => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
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
     * Relation vers le catalogue des types d'actes
     */
    public function typeActeRelation(): BelongsTo
    {
        return $this->belongsTo(TypeActe::class, 'type_acte', 'type_acte');
    }

    /**
     * Accessor pour obtenir le nom lisible du type d'acte
     */
    public function getNomActeAttribute(): string
    {
        if ($this->typeActeRelation && isset($this->typeActeRelation->nom)) {
            return $this->typeActeRelation->nom;
        }

        return ucfirst($this->type_acte ?? 'Acte administratif');
    }
    /**
    * Calcule dynamiquement le prix unitaire basé sur la relation TypeActe
    */
    public function calculerPrixUnitaire(string $langue = 'MG', string $mode = 'standard'): float
    {
        if ($this->typeActeRelation) {
            return $this->typeActeRelation->getPrix($langue, $mode);
        }

        return (float) ($this->prix_unitaire ?? 0);
    }

    /**
    * Recalcule et met à jour le sous-total de l'acte
    */
    public function calculerSousTotal(string $langue = 'MG', string $mode = 'standard'): float
    {
        $prixUnitaire = $this->calculerPrixUnitaire($langue, $mode);
        return $prixUnitaire * ($this->quantite ?? 1);
    }
}