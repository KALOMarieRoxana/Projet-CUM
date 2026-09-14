<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_acte_supplements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_acte_id');
            $table->string('nom');           // Ex: "Bulletin de naissance"
            $table->string('code')->unique(); // Ex: "bulletin_naissance"
            $table->text('description')->nullable();
            
            // Prix par langue et service
            $table->decimal('prix_standard_fr', 10, 2)->default(0);
            $table->decimal('prix_express_fr', 10, 2)->default(0);
            $table->decimal('prix_standard_mg', 10, 2)->default(0);
            $table->decimal('prix_express_mg', 10, 2)->default(0);
            
            $table->boolean('actif')->default(true);
            $table->integer('ordre')->default(0);
            
            $table->timestamps();

            $table->foreign('type_acte_id')
                  ->references('id')
                  ->on('type_actes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_acte_supplements');
    }
};