<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Citoyen extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'citoyens';
    protected $primaryKey = 'id_citoyens';
    public $timestamps = true;

    protected $fillable = [
        'nom',
        'prenom',
        'adresse',
        'contact',
        'relation',
        'email',
        'password',
        'cin_recto',
        'cin_verso',
        'actif',
        'desactive_le',
        'raison_desactivation',
    ];

    protected $casts = [
        'actif'        => 'boolean',
        'desactive_le' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ✅ Accessor : statut lisible
    public function getStatutCompteAttribute()
    {
        return $this->actif ? 'Actif' : 'Désactivé';
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function demandes()
    {
        return $this->hasMany(Demande::class, 'id_citoyens', 'id_citoyens');
    }
}
