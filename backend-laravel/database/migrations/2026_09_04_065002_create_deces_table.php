<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deces', function (Blueprint $table) {
            $table->id();
            
            // Paramètres de l'acte / Tarification
            $table->string('langue'); // 'MG' ou 'FR'
            $table->string('type_service'); // 'standard' ou 'express'
            $table->string('sigle');
            $table->decimal('montantExpressMG', 10, 2)->default(7000.00);
            $table->decimal('montantStandardMG', 10, 2)->default(9000.00);
            $table->decimal('montantStandardFR', 10, 2)->default(10000.00);
            $table->decimal('montantExpressFR', 10, 2)->default(15000.00);
            
            // Informations sur le défunt
            $table->string('nom_defunt');
            $table->string('prenom_defunt');
            $table->date('date_naissance_defunt')->nullable();
            $table->date('date_deces');
            $table->string('lieu_deces');
            $table->string('num_acte');
            
            // Informations filiation (optionnel pour l'acte de décès)
            $table->string('nom_pere_defunt')->nullable();
            $table->string('prenom_pere_defunt')->nullable();
            $table->string('nom_mere_defunt')->nullable();
            $table->string('prenom_mere_defunt')->nullable();
            
            // Quantité demandée
            $table->integer('nbre_com');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deces');
    }
};