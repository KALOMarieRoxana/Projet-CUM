<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Dtatabase\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Demande extends Model
{
    use HasFactory;
    /**
     * Nom de la table associée au modèle
     */
    protected $table = 'demandes';

    /**
     * Clé primaire de la table
     */

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
        'nombre_actes' => 'integer',
        'date_traitement' => 'datetime',
        'prix_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONS ELOQUENT
    // ============================================================
    public function citoyen(): BelongsTo
    {
        return $this->belongsTo(Citoyen::class, 'citoyen_id');
    }

    public function traiteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function demandeActes(): HasMany
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
    /**
     * Recalcule et met à jour automatiquement le prix total et le nombre d'actes
     */
    public function calculerTotaux(): void
    {
        $this->nombre_actes = $this->demandeActes()->sum('quantite');
        $this->prix_total   = $this->demandeActes()->sum('sous_total');
        $this->save();
    }
}