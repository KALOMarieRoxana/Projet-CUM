<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('divorces', function (Blueprint $table) {
            $table->date('date_mariage')->nullable()->after('prenom_epouse');
        });
    }

    public function down(): void
    {
        Schema::table('divorces', function (Blueprint $table) {
            $table->dropColumn('date_mariage');
        });
    }
};