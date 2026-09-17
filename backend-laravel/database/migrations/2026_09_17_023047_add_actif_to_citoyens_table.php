<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('citoyens', function (Blueprint $table) {
            $table->boolean('actif')->default(true)->after('email');
            $table->timestamp('desactive_le')->nullable()->after('actif');
            $table->text('raison_desactivation')->nullable()->after('desactive_le');
        });
    }

    public function down()
    {
        Schema::table('citoyens', function (Blueprint $table) {
            $table->dropColumn(['actif', 'desactive_le', 'raison_desactivation']);
        });
    }
};