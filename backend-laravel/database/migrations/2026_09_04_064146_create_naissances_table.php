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
        Schema::create('naissances', function (Blueprint $table) {
            $table->id(); // Equivalent à bigint(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
            
            // Paramètres de l'acte / Tarification
            $table->string('langue');
            $table->string('type_service');
            $table->string('sigle');
            $table->decimal('montantExpressMG', 10, 2)->default(7000.00);
            $table->decimal('montantStandardMG', 10, 2)->default(3000.00);
            $table->decimal('montantStandardFR', 10, 2)->default(7000.00);
            $table->decimal('montantExpressFR', 10, 2)->default(12000.00);
            
            // Informations sur la personne
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('num_acte');
            
            // Informations sur les parents
            $table->string('nom_pere');
            $table->string('prenom_pere');
            $table->string('nom_mere');
            $table->string('prenom_mere');
            
            // Quantité demandée
            $table->integer('nbre_com');
            
            // Horodatage (created_at et updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('naissances');
    }
};