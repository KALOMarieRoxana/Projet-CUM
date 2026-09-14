<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeActe extends Model
{
    protected $table = 'demande_actes';

    protected $fillable = [
        'demande_id',
        'type_acte_id',        // ✅ C'est type_acte_id
        'supplement_id',
        'acte_type',
        'acte_id',
        'langue',
        'prix_acte',
        'prix_supplement',
        'prix_unitaire',
        'quantite',
        'quantite_supplement',
        'sous_total',
        'statut',
        'commentaire',
        'date_traitement',
    ];

    protected $casts = [
        'prix_acte' => 'decimal:2',
        'prix_supplement' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'sous_total' => 'decimal:2',
        'date_traitement' => 'datetime',
    ];

    // ✅ Relation avec la demande
    public function demande()
    {
        return $this->belongsTo(Demande::class, 'demande_id', 'id_demande');
    }

    // ✅ Relation avec le supplément
    public function supplement()
    {
        return $this->belongsTo(TypeActeSupplement::class, 'supplement_id', 'id');
    }

    // ✅ CORRECTION : Utiliser type_acte_id (pas type_acte)
    public function typeActe()
    {
        return $this->belongsTo(TypeActe::class, 'type_acte_id', 'id');
    }

    // ✅ Alias
    public function typeActeRelation()
    {
        return $this->belongsTo(TypeActe::class, 'type_acte_id', 'id');
    }

    // ✅ Relation polymorphe
    public function acte()
    {
        return $this->morphTo('acte', 'acte_type', 'acte_id');
    }
}