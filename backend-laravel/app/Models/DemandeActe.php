<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeActe extends Model
{
    use HasFactory;

    protected $fillable = [
        'demande_id',
        'type_acte_id',
        'acte_type',
        'acte_id',
        'prix_unitaire',
        'quantite',
        'sous_total',
        'statut',
        'commentaire',
        'date_traitement'
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'sous_total' => 'decimal:2',
        'date_traitement' => 'datetime'
    ];

    // Relations
    public function demande()
    {
        return $this->belongsTo(Demande::class, 'demande_id', 'id_demande');
    }

    public function typeActe()
    {
        return $this->belongsTo(TypeActe::class, 'type_acte_id', 'id');
    }

    // Relation polymorphique vers l'acte spécifique
    public function Acte()
    {
        return $this->morphTo('acte', 'acte_type', 'acte_id');
    }

    // Accesseurs
    public function getTypeActeLabelAttribute()
    {
        return $this->typeActe?->nom ?? $this->type_acte;
    }

    public function getTypeActeSlugAttribute()
    {
        return $this->typeActe?->type_acte;
    }

    public function getSousTotalAttribute($value)
    {
        if ($value == 0 && $this->prix_unitaire > 0) {
            return $this->prix_unitaire * $this->quantite;
        }
        return $value;
    }

    // Mutateur pour calculer automatiquement le sous-total
    public function setSousTotalAttribute($value)
    {
        if ($value == null) {
            $this->attributes['sous_total'] = $this->prix_unitaire * $this->quantite;
        } else {
            $this->attributes['sous_total'] = $value;
        }
    }
}