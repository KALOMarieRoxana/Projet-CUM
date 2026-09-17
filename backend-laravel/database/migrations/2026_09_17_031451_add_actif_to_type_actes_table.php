<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('type_actes', function (Blueprint $table) {
            $table->boolean('actif')->default(true)->after('montantExpressFR');
        });
    }

    public function down()
    {
        Schema::table('type_actes', function (Blueprint $table) {
            $table->dropColumn('actif');
        });
    }
};