<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_actes', function (Blueprint $table) {
            $table->id();

            // 1. Clé étrangère vers la demande globale
            $table->unsignedBigInteger('demande_id');
            $table->foreign('demande_id')
                  ->references('id_demande')
                  ->on('demandes')
                  ->onDelete('cascade');

            // 2. Clé étrangère vers type_actes (utiliser l'ID au lieu du slug)
            $table->unsignedBigInteger('type_acte_id');
            $table->foreign('type_acte_id')
                  ->references('id')
                  ->on('type_actes')
                  ->onDelete('cascade');

            // 3. Relation Polymorphique vers la table d'acte (naissances, mariages, etc.)
            $table->string('acte_type'); // Ex: "App\Models\Naissance"
            $table->unsignedBigInteger('acte_id');

            // 4. Tarification et détails
            $table->decimal('prix_unitaire', 10, 2)->default(0.00);
            $table->integer('quantite')->default(1);
            $table->decimal('sous_total', 10, 2)->default(0.00);

            // 5. Suivi du traitement
            $table->enum('statut', ['en_attente', 'accepté', 'refusé'])->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->timestamp('date_traitement')->nullable();

            $table->timestamps();

            // ✅ Index pour améliorer les performances
            $table->index(['acte_type', 'acte_id']);
            $table->index('type_acte_id');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_actes');
    }
};