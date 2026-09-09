<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeActe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'type_acte',
        'sigle',
        'montantStandardMG',
        'montantExpressMG',
        'montantStandardFR',
        'montantExpressFR'
    ];

    protected $casts = [
        'montantStandardMG' => 'decimal:2',
        'montantExpressMG' => 'decimal:2',
        'montantStandardFR' => 'decimal:2',
        'montantExpressFR' => 'decimal:2'
    ];

    // ========== RELATIONS ==========
    
    /**
     * Relation vers demande_actes
     */
    public function demandeActes()
    {
        return $this->hasMany(DemandeActe::class, 'type_acte_id', 'id');
    }

    /**
     * Récupérer tous les actes de ce type
     * (Relation polymorphique inverse)
     */
    public function actes()
    {
        // Cette méthode permet de récupérer tous les actes d'un type spécifique
        switch ($this->type_acte) {
            case 'naissance':
                return $this->hasManyThrough(Naissance::class, DemandeActe::class, 'type_acte_id', 'id', 'id', 'acte_id');
            case 'mariage':
                return $this->hasManyThrough(Mariage::class, DemandeActe::class, 'type_acte_id', 'id', 'id', 'acte_id');
            case 'deces':
                return $this->hasManyThrough(Deces::class, DemandeActe::class, 'type_acte_id', 'id', 'id', 'acte_id');
            case 'divorces':
                return $this->hasManyThrough(Divorce::class, DemandeActe::class, 'type_acte_id', 'id', 'id', 'acte_id');
            default:
                return null;
        }
    }

    // ========== MÉTHODES ==========

    /**
     * Calculer le prix selon la langue et le service
     */
    public function calculerPrix(string $langue, string $service): float
    {
        $field = 'montant' . ucfirst($service) . strtoupper($langue);
        return $this->$field ?? 0;
    }

    // ========== ACCESSORS ==========
    
    /**
     * Accesseur pour le libellé
     */
    public function getLabelAttribute()
    {
        return $this->nom;
    }

    /**
     * Accesseur pour le slug
     */
    public function getSlugAttribute()
    {
        return $this->type_acte;
    }
}