<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('demandes', function (Blueprint $table) {
            // ✅ Colonnes paiement
            $table->boolean('est_paye')->default(false)->after('statut');
            $table->timestamp('date_paiement')->nullable()->after('est_paye');
            $table->foreignId('encaisse_par')->nullable()->after('date_paiement');
        });
    }

    public function down()
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn(['est_paye', 'date_paiement', 'encaisse_par']);
        });
    }
};