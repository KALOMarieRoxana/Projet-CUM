<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Mariage extends Model
{
    use HasFactory;

    protected $table = 'mariages';

    protected $fillable = [
        // Informations Époux
        'nom_epoux',
        'prenom_epoux',
        'date_naissance_epoux',
        'lieu_naissance_epoux',

        // Informations Épouse
        'nom_epouse',
        'prenom_epouse',
        'date_naissance_epouse',
        'lieu_naissance_epouse',

        // Informations sur le Mariage
        'date_mariage',
        'lieu_mariage',
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
        'date_naissance_epoux' => 'date',
        'date_naissance_epouse' => 'date',
        'date_mariage' => 'date',
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
     * Nom complet de l'époux
     */
    public function getEpouxCompletAttribute(): string
    {
        return $this->prenom_epoux . ' ' . $this->nom_epoux;
    }

    /**
     * Nom complet de l'épouse
     */
    public function getEpouseCompletAttribute(): string
    {
        return $this->prenom_epouse . ' ' . $this->nom_epouse;
    }

    /**
     * Type d'acte
     */
    public function getTypeActeAttribute(): string
    {
        return 'mariage';
    }

    /**
     * Libellé de l'acte
     */
    public function getLibelleActeAttribute(): string
    {
        return 'Acte de mariage';
    }

    /**
     * Sigle
     */
    public function getSigleActeAttribute(): string
    {
        return 'AM';
    }

    /**
     * Date de naissance de l'époux formatée
     */
    public function getDateNaissanceEpouxFormateeAttribute(): string
    {
        return $this->date_naissance_epoux ? $this->date_naissance_epoux->format('d/m/Y') : '';
    }

    /**
     * Date de naissance de l'épouse formatée
     */
    public function getDateNaissanceEpouseFormateeAttribute(): string
    {
        return $this->date_naissance_epouse ? $this->date_naissance_epouse->format('d/m/Y') : '';
    }

    /**
     * Date de mariage formatée
     */
    public function getDateMariageFormateeAttribute(): string
    {
        return $this->date_mariage ? $this->date_mariage->format('d/m/Y') : '';
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