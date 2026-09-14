<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            if (!Schema::hasColumn('demandes', 'pdf_path')) {
                $table->string('pdf_path')->nullable()->after('statut');
            }
            if (!Schema::hasColumn('demandes', 'pdf_genere_at')) {
                $table->timestamp('pdf_genere_at')->nullable()->after('pdf_path');
            }
            if (!Schema::hasColumn('demandes', 'notification_lue')) {
                $table->boolean('notification_lue')->default(false)->after('pdf_genere_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'pdf_genere_at', 'notification_lue']);
        });
    }
};