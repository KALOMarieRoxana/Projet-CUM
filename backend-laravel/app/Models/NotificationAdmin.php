<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationAdmin extends Model
{
    protected $table = 'notifications_admin';

    protected $fillable = [
        'type',
        'titre',
        'message',
        'demande_id',
        'reference',
        'lue',
        'user_id',
    ];

    protected $casts = [
        'lue' => 'boolean',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'demande_id', 'id_demande');
    }
}