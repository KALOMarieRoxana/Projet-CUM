<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            if (!Schema::hasColumn('demandes', 'delai_heures')) {
                $table->integer('delai_heures')->default(72)->after('service');
            }
            if (!Schema::hasColumn('demandes', 'date_estimation')) {
                $table->timestamp('date_estimation')->nullable()->after('date_traitement');
            }
            if (!Schema::hasColumn('demandes', 'estimation_envoyee')) {
                $table->boolean('estimation_envoyee')->default(false)->after('date_estimation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn(['delai_heures', 'date_estimation', 'estimation_envoyee']);
        });
    }
};