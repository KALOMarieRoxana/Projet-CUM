<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeActeSupplement extends Model
{
    protected $table = 'type_acte_supplements';

    protected $fillable = [
        'type_acte_id',
        'nom',
        'code',
        'description',
        'prix_standard_fr',
        'prix_express_fr',
        'prix_standard_mg',
        'prix_express_mg',
        'actif',
        'ordre',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'prix_standard_fr' => 'decimal:2',
        'prix_express_fr' => 'decimal:2',
        'prix_standard_mg' => 'decimal:2',
        'prix_express_mg' => 'decimal:2',
    ];

    public function typeActe()
    {
        return $this->belongsTo(TypeActe::class, 'type_acte_id', 'id');
    }

    public function getPrix($langue = 'fr', $service = 'standard')
    {
        $champ = "prix_{$service}_{$langue}";
        return $this->$champ ?? 0;
    }
}