<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Naissance extends Model
{
    use HasFactory;

    protected $table = 'naissances';

    protected $fillable = [
        // Identité de la personne concernée
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'num_acte',

        // Informations des parents
        'nom_pere',
        'prenom_pere',
        'nom_mere',
        'prenom_mere',

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
        'date_naissance' => 'date',
        'date_acte' => 'date',
        'montantStandardMG' => 'decimal:2',
        'montantExpressMG' => 'decimal:2',
        'montantStandardFR' => 'decimal:2',
        'montantExpressFR' => 'decimal:2',
        'nbre_com' => 'integer',
    ];

    /**
     * Relation polymorphique vers demande_actes
     * Chaque naissance est liée à un acte dans la table demande_actes
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
            'acte_id',      // Clé étrangère sur demande_actes
            'id_demande',   // Clé étrangère sur demandes
            'id',           // Clé locale sur naissances
            'demande_id'    // Clé locale sur demande_actes
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
     * Accesseur pour le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Accesseur pour le nom complet du père
     */
    public function getPereCompletAttribute(): string
    {
        return $this->prenom_pere . ' ' . $this->nom_pere;
    }

    /**
     * Accesseur pour le nom complet de la mère
     */
    public function getMereCompletAttribute(): string
    {
        return $this->prenom_mere . ' ' . $this->nom_mere;
    }

    /**
     * Accesseur pour le type d'acte (pour l'affichage)
     */
    public function getTypeActeAttribute(): string
    {
        return 'naissance';
    }

    /**
     * Accesseur pour le libellé de l'acte
     */
    public function getLibelleActeAttribute(): string
    {
        return 'Acte de naissance';
    }

    /**
     * Accesseur pour le sigle
     */
    public function getSigleActeAttribute(): string
    {
        return 'AN';
    }

    /**
     * Formatage de la date de naissance
     */
    public function getDateNaissanceFormateeAttribute(): string
    {
        return $this->date_naissance ? $this->date_naissance->format('d/m/Y') : '';
    }

    /**
     * Formatage de la date de l'acte
     */
    public function getDateActeFormateeAttribute(): string
    {
        return $this->date_acte ? $this->date_acte->format('d/m/Y') : '';
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

    /**
     * Récupérer le prix depuis la table type_actes
     */
    public function getPrixDepuisTypeActe(string $langue, string $service): float
    {
        if ($this->typeActe) {
            return $this->typeActe->calculerPrix($langue, $service);
        }
        return 0;
    }
}