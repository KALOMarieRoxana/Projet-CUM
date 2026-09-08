<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeActe extends Model
{
    use HasFactory;

    protected $table = 'type_actes';

    protected $fillable = [
        'nom',
        'type_acte',
        'sigle',
        'montantStandardMG',
        'montantExpressMG',
        'montantStandardFR',
        'montantExpressFR',
    ];

    protected $casts = [
        'montantStandardMG' => 'decimal:2',
        'montantExpressMG'  => 'decimal:2',
        'montantStandardFR' => 'decimal:2',
        'montantExpressFR'  => 'decimal:2',
    ];

    /**
     * Calcule le prix unitaire selon la langue et le mode de traitement
     *
     * @param string $langue  ('MG' ou 'FR')
     * @param string $mode    ('standard' ou 'express')
     * @return float
     */
    public function getPrix(string $langue = 'MG', string $mode = 'standard'): float
    {
        $langue = strtoupper($langue);
        $mode   = strtolower($mode);

        if ($langue === 'FR') {
            return (float) ($mode === 'express' ? $this->montantExpressFR : $this->montantStandardFR);
        }

        return (float) ($mode === 'express' ? $this->montantExpressMG : $this->montantStandardMG);
    }

    /**
     * Calcule le prix total en fonction de la quantité
     *
     * @param int $quantite
     * @param string $langue
     * @param string $mode
     * @return float
     */
    public function getPrixTotal(int $quantite = 1, string $langue = 'MG', string $mode = 'standard'): float
    {
        return $this->getPrix($langue, $mode) * $quantite;
    }
}