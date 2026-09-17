<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->string('statut_avant_archive')->nullable()->after('statut');
        });
    }

    public function down()
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn('statut_avant_archive');
        });
    }
};