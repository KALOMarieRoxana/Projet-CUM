<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // protected $table = 'utilisateurs'; // Il est préférable de laisser Laravel gérer le nom de la table 'users' par défaut.

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'mot_de_passe',
        'contact',
        'adresse',
        'relation',
    ];

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relation avec les demandes
    public function demandes()
    {
        return $this->hasMany(Demande::class, 'user_id', 'id');
    }
}
