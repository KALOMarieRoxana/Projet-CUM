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
        'email_verified_at',
        'email_verification_token',
    ];

    protected $casts = [
        'actif'        => 'boolean',
        'desactive_le' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token'
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
        return $this->hasMany(Demande::class, 'citoyen_id', 'id_citoyens');
    }
}
