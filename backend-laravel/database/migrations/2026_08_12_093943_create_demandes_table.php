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
            
            $table->unsignedBigInteger('citoyen_id');
            $table->foreign('citoyen_id')->references('id_citoyens')->on('citoyens')->onDelete('cascade');
            
            $table->string('demandeur_nom');
            $table->string('demandeur_prenom');
            $table->string('demandeur_adresse');
            $table->string('demandeur_relation')->nullable();
            $table->string('demandeur_contact');
            
            $table->string('personne_nom');
            $table->string('personne_prenom');
            $table->string('personne_lieu_naissance');
            $table->date('personne_date_naissance');
            
            $table->enum('service', ['standard', 'express'])->default('standard');
            $table->decimal('prix_total', 10, 2)->default(0);
            $table->integer('nombre_actes')->default(0);
            
            $table->enum('statut', ['en_attente', 'acceptée', 'refusée', 'partiellement_acceptée'])->default('en_attente');
            $table->timestamp('date_traitement')->nullable();
            $table->text('commentaire_admin')->nullable();
            
            $table->unsignedBigInteger('traite_par')->nullable();
            $table->foreign('traite_par')->references('id')->on('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};