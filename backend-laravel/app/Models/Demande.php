<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    use HasFactory;

    protected $table = 'demandes';
    protected $primaryKey = 'id_demande';

    protected $fillable = [
        'user_id',
        'type_acte_id',
        'demandeur_nom',
        'demandeur_prenom',
        'demandeur_adresse',
        'demandeur_relation',
        'demandeur_contact',
        'personne_nom',
        'personne_prenom',
        'personne_numero_acte',
        'personne_lieu_naissance',
        'personne_date_naissance',
        'service',
        'prix',
        'statut',
        'date_traitement',
    ];

    protected $casts = [
        'personne_date_naissance' => 'date',
        'date_traitement' => 'datetime',
        'prix' => 'decimal:2',
    ];

    // Relation avec l'utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation avec le type d'acte
    public function typeActe()
    {
        return $this->belongsTo(TypeActe::class, 'type_acte_id');
    }
}
