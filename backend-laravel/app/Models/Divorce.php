<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Divorce extends Model
{
    use HasFactory;

    protected $table = 'divorces';

    protected $fillable = [
        // Informations des ex-époux
        'nom_epoux',
        'prenom_epoux',
        'nom_epouse',
        'prenom_epouse',

        // Informations sur le jugement/transcription
        'date_jugement',
        'date_mariage',
        'num_jugement',
        'tribunal',
        'num_acte',
        'motif',

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
        'date_jugement' => 'date',
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
        return 'divorces';
    }

    /**
     * Libellé de l'acte
     */
    public function getLibelleActeAttribute(): string
    {
        return 'Acte de divorce';
    }

    /**
     * Sigle
     */
    public function getSigleActeAttribute(): string
    {
        return 'ADV';
    }

    /**
     * Date du jugement formatée
     */
    public function getDateJugementFormateeAttribute(): string
    {
        return $this->date_jugement ? $this->date_jugement->format('d/m/Y') : '';
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