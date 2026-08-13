<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeActe extends Model
{
    use HasFactory;

    protected $table = 'type_actes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nom',
        'type_acte',
        'prix_standard',
        'prix_express',
    ];

    // Relation avec les demandes
    public function demandes()
    {
        return $this->hasMany(Demande::class, 'type_acte_id');
    }
}