<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_actes', function (Blueprint $table) {
            
            // ✅ 1. Colonne supplement_id
            if (!Schema::hasColumn('demande_actes', 'supplement_id')) {
                $table->unsignedBigInteger('supplement_id')->nullable()->after('type_acte_id');
                $table->foreign('supplement_id')
                      ->references('id')
                      ->on('type_acte_supplements')
                      ->onDelete('set null');
            }

            // ✅ 2. Colonne langue
            if (!Schema::hasColumn('demande_actes', 'langue')) {
                $table->string('langue')->default('fr')->after('supplement_id');
            }

            // ✅ 3. Prix acte séparé
            if (!Schema::hasColumn('demande_actes', 'prix_acte')) {
                $table->decimal('prix_acte', 10, 2)->default(0)->after('langue');
            }

            // ✅ 4. Prix supplément séparé
            if (!Schema::hasColumn('demande_actes', 'prix_supplement')) {
                $table->decimal('prix_supplement', 10, 2)->default(0)->after('prix_acte');
            }

            // ✅ 5. Quantité supplément
            if (!Schema::hasColumn('demande_actes', 'quantite_supplement')) {
                $table->integer('quantite_supplement')->default(0)->after('quantite');
            }
        });
    }

    public function down(): void
    {
        Schema::table('demande_actes', function (Blueprint $table) {
            // Supprimer la clé étrangère d'abord
            if (Schema::hasColumn('demande_actes', 'supplement_id')) {
                $table->dropForeign(['supplement_id']);
            }

            // Supprimer les colonnes
            $table->dropColumn([
                'supplement_id',
                'langue',
                'prix_acte',
                'prix_supplement',
                'quantite_supplement'
            ]);
        });
    }
};