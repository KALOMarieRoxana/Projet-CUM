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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function demandes()
    {
        return $this->hasMany(Demande::class, 'id_citoyens', 'id_citoyens');
    }
}
