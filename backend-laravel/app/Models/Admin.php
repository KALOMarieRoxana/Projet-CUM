<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'admins';
    protected $primaryKey = 'id_admins';

    protected $fillable = ['nom', 'email', 'mot-de-passe', 'contact'];

    protected $hidden = ['mot-de-passe'];

    public function getAuthPassword()
    {
        return $this->attributes['mot-de-passe'];
    }
}