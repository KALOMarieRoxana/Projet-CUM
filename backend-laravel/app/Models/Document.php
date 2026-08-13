<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';
    protected $primaryKey = 'id_documents';

    const CREATED_AT = 'created-at';
    const UPDATED_AT = 'updated-at';

    protected $fillable = ['id_demande', 'reference', 'nom-fichier', 'chemin-fichier'];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'id_demande', 'id_demande');
    }
}
