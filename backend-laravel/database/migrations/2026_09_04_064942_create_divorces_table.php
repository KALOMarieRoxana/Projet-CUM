<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divorces', function (Blueprint $table) {
            $table->id();
            
            // Paramètres de l'acte / Tarification
            $table->string('langue'); // 'MG' ou 'FR'
            $table->string('type_service'); // 'standard' ou 'express'
            $table->string('sigle');
            $table->decimal('montantExpressMG', 10, 2)->default(60000.00);
            $table->decimal('montantStandardMG', 10, 2)->default(40000.00);
            $table->decimal('montantStandardFR', 10, 2)->default(50000.00);
            $table->decimal('montantExpressFR', 10, 2)->default(70000.00);
            
            // Informations sur les ex-époux
            $table->string('nom_epoux');
            $table->string('prenom_epoux');
            $table->string('nom_epouse');
            $table->string('prenom_epouse');
            
            // Informations sur le jugement/transcription
            $table->date('date_jugement');
            $table->string('tribunal'); // Ex: Tribunal de Première Instance d'Antananarivo
            $table->string('num_transcription'); // Numéro d'acte/transcription
            
            // Quantité demandée
            $table->integer('nbre_com');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divorces');
    }
};