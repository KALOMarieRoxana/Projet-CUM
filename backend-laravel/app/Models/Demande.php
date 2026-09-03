<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_demande';

    protected $fillable = [
        'reference',
        'citoyen_id',
        'demandeur_nom',
        'demandeur_prenom',
        'demandeur_adresse',
        'demandeur_relation',
        'demandeur_contact',
        'personne_nom',
        'personne_prenom',
        'personne_lieu_naissance',
        'personne_date_naissance',
        'service',
        'prix_total',
        'nombre_actes',
        'statut',
        'date_traitement',
        'commentaire_admin',
        'traite_par',
    ];

    protected $casts = [
        'personne_date_naissance' => 'date',
        'date_traitement' => 'datetime',
        'prix_total' => 'decimal:2',
    ];

    // ============================================================
    // RELATIONS
    // ============================================================
    public function citoyen()
    {
        return $this->belongsTo(Citoyen::class, 'citoyen_id', 'id_citoyens');
    }

    public function traiteur()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function actes()
    {
        return $this->hasMany(DemandeActe::class, 'demande_id', 'id_demande');
    }

    // ============================================================
    // SCOPES
    // ============================================================
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeAcceptee($query)
    {
        return $query->where('statut', 'acceptée');
    }

    public function scopeRefusee($query)
    {
        return $query->where('statut', 'refusée');
    }

    // ============================================================
    // ACCESSORS
    // ============================================================
    public function getStatutLibelleAttribute()
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'acceptée' => 'Acceptée',
            'refusée' => 'Refusée',
            'partiellement_acceptée' => 'Partiellement acceptée',
            default => $this->statut,
        };
    }

    public function getStatutCouleurAttribute()
    {
        return match($this->statut) {
            'en_attente' => 'warning',
            'acceptée' => 'success',
            'refusée' => 'danger',
            'partiellement_acceptée' => 'info',
            default => 'secondary',
        };
    }

    // ============================================================
    // GÉNÉRATION DE RÉFÉRENCE
    // ============================================================
    public static function generateReference()
    {
        $prefix = 'DEM';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -6));
        return $prefix . $year . $month . '-' . $random;
    }
}