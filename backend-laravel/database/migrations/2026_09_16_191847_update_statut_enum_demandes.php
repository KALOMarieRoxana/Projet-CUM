<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE demandes
            MODIFY COLUMN statut
            ENUM('en_attente', 'acceptée', 'refusée', 'partiellement_traitée', 'archivée')
            DEFAULT 'en_attente'
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE demandes
            MODIFY COLUMN statut
            ENUM('en_attente', 'acceptée', 'refusée', 'partiellement_traitée')
            DEFAULT 'en_attente'
        ");
    }
};