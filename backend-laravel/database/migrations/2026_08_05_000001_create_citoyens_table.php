<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('citoyens', function (Blueprint $table) {
            $table->id('id_citoyens');
            $table->string('nom');
            $table->string('prenom');
            $table->string('adresse')->nullable();
            $table->string('contact')->nullable();
            $table->string('relation')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('cin_recto')->nullable();
            $table->string('cin_verso')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citoyens');
    }
};
