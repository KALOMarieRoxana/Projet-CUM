<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemandesTable extends Migration
{
    public function up()
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id('id_demande');
            
            // Utiliser unsignedBigInteger avec foreign() au lieu de foreignId
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('type_acte_id');
            
            // Ajouter les contraintes de clé étrangère
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->foreign('type_acte_id')
                  ->references('id')
                  ->on('type_actes')
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
            $table->string('personne_numero_acte')->nullable();
            $table->string('personne_lieu_naissance');
            $table->date('personne_date_naissance');
            
            // Service et prix
            $table->enum('service', ['standard', 'express'])->default('standard');
            $table->decimal('prix', 10, 2);
            
            // Statut
            $table->enum('statut', ['en attente', 'acceptée', 'refusée'])->default('en attente');
            $table->timestamp('date_traitement')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('demandes');
    }
}