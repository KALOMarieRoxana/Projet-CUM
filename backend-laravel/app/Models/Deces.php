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
        'cause_deces',
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

    protected $casts = [
        'date_naissance_defunt' => 'date',
        'date_deces' => 'date',
        'montantStandardMG' => 'decimal:2',
        'montantExpressMG' => 'decimal:2',
        'montantStandardFR' => 'decimal:2',
        'montantExpressFR' => 'decimal:2',
        'nbre_com' => 'integer',
    ];

    /**
     * Relation polymorphique vers demande_actes
     */
    public function demandeActe(): MorphOne
    {
        return $this->morphOne(DemandeActe::class, 'acte');
    }

    /**
     * Relation pour récupérer la demande complète via demande_actes
     */
    public function demande()
    {
        return $this->hasOneThrough(
            Demande::class,
            DemandeActe::class,
            'acte_id',
            'id_demande',
            'id',
            'demande_id'
        );
    }

    /**
     * Relation pour récupérer le type d'acte via demande_actes
     */
    public function typeActe()
    {
        return $this->hasOneThrough(
            TypeActe::class,
            DemandeActe::class,
            'acte_id',
            'id',
            'id',
            'type_acte_id'
        );
    }

    // ========== ACCESSORS ==========

    /**
     * Nom complet du défunt
     */
    public function getDefuntCompletAttribute(): string
    {
        return $this->prenom_defunt . ' ' . $this->nom_defunt;
    }

    /**
     * Type d'acte
     */
    public function getTypeActeAttribute(): string
    {
        return 'deces';
    }

    /**
     * Libellé de l'acte
     */
    public function getLibelleActeAttribute(): string
    {
        return 'Acte de décès';
    }

    /**
     * Sigle
     */
    public function getSigleActeAttribute(): string
    {
        return 'AD';
    }

    /**
     * Date de naissance du défunt formatée
     */
    public function getDateNaissanceDefuntFormateeAttribute(): string
    {
        return $this->date_naissance_defunt ? $this->date_naissance_defunt->format('d/m/Y') : '';
    }

    /**
     * Date de décès formatée
     */
    public function getDateDecesFormateeAttribute(): string
    {
        return $this->date_deces ? $this->date_deces->format('d/m/Y') : '';
    }

    /**
     * Âge au moment du décès
     */
    public function getAgeAuDecesAttribute(): ?int
    {
        if ($this->date_naissance_defunt && $this->date_deces) {
            return $this->date_naissance_defunt->diffInYears($this->date_deces);
        }
        return null;
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
}