<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mariages', function (Blueprint $table) {
            $table->id();
            
            // Paramètres de l'acte / Tarification
            $table->string('langue'); // 'MG' ou 'FR'
            $table->string('type_service'); // 'standard' ou 'express'
            $table->string('sigle');
            $table->decimal('montantExpressMG', 10, 2)->default(50000.00);
            $table->decimal('montantStandardMG', 10, 2)->default(30000.00);
            $table->decimal('montantStandardFR', 10, 2)->default(40000.00);
            $table->decimal('montantExpressFR', 10, 2)->default(60000.00);
            
            // Informations sur l'Époux
            $table->string('nom_epoux');
            $table->string('prenom_epoux');
            $table->date('date_naissance_epoux')->nullable();
            
            // Informations sur l'Épouse
            $table->string('nom_epouse');
            $table->string('prenom_epouse');
            $table->date('date_naissance_epouse')->nullable();
            
            // Informations sur le mariage
            $table->date('date_mariage');
            $table->string('lieu_mariage');
            $table->string('num_acte');
            
            // Quantité demandée
            $table->integer('nbre_com');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mariages');
    }
};