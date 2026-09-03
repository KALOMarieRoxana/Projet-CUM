<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id('id_demande');
            $table->string('reference')->unique();
            
            // ⚠️ Clé étrangère vers citoyens.id_citoyens (explicite)
            $table->unsignedBigInteger('citoyen_id');
            $table->foreign('citoyen_id')
                  ->references('id_citoyens')
                  ->on('citoyens')
                  ->onDelete('cascade');
            
            // Informations du demandeur
            $table->string('demandeur_nom');
            $table->string('demandeur_prenom');
            $table->string('demandeur_adresse');
            $table->string('demandeur_relation')->nullable();
            $table->string('demandeur_contact');
            
            // Informations de la personne concernée
            $table->string('personne_nom');
            $table->string('personne_prenom');
            $table->string('personne_lieu_naissance');
            $table->date('personne_date_naissance');
            
            // Service et prix
            $table->enum('service', ['standard', 'express'])->default('standard');
            $table->decimal('prix_total', 10, 2)->default(0);
            $table->integer('nombre_actes')->default(0);
            
            // Statut
            $table->enum('statut', ['en_attente', 'acceptée', 'refusée', 'partiellement_acceptée'])->default('en_attente');
            $table->timestamp('date_traitement')->nullable();
            $table->text('commentaire_admin')->nullable();
            
            // ⚠️ Clé étrangère vers users.id (explicite)
            $table->unsignedBigInteger('traite_par')->nullable();
            $table->foreign('traite_par')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            $table->timestamps();
        });

        // Table demande_actes
        Schema::create('demande_actes', function (Blueprint $table) {
            $table->id();
            
            // ⚠️ Clé étrangère vers demandes.id_demande (explicite)
            $table->unsignedBigInteger('demande_id');
            $table->foreign('demande_id')
                  ->references('id_demande')
                  ->on('demandes')
                  ->onDelete('cascade');
            
            // ⚠️ Clé étrangère vers type_actes.id (explicite)
            $table->unsignedBigInteger('type_acte_id');
            $table->foreign('type_acte_id')
                  ->references('id')
                  ->on('type_actes')
                  ->onDelete('cascade');
            
            $table->string('personne_numero_acte')->nullable();
            $table->decimal('prix_unitaire', 10, 2)->default(0);
            $table->enum('statut', ['en_attente', 'accepté', 'refusé'])->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->timestamp('date_traitement')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_actes');
        Schema::dropIfExists('demandes');
    }
};