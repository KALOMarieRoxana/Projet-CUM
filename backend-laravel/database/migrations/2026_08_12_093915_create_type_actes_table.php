<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_actes', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Ex: "Acte de naissance", "Acte de mariage"
            $table->string('type_acte')->unique(); // 'naissance', 'mariage', 'deces', 'divorce'
            $table->string('sigle'); // Ex: "AN", "AM", "AD", "ADV"
            
            // Grille tarifaire par défaut pour chaque type d'acte
            $table->decimal('montantStandardMG', 10, 2)->default(3000.00);
            $table->decimal('montantExpressMG', 10, 2)->default(7000.00);
            $table->decimal('montantStandardFR', 10, 2)->default(7000.00);
            $table->decimal('montantExpressFR', 10, 2)->default(12000.00);
            
            $table->timestamps();
        });

        // Insertion des 4 types d'actes par défaut
        DB::table('type_actes')->insert([
            [
                'nom'               => 'Acte de naissance',
                'type_acte'         => 'naissance',
                'sigle'             => 'AN',
                'montantStandardMG'   => 3000.00,
                'montantExpressMG'  => 7000.00,
                'montantStandardFR'   => 7000.00,
                'montantExpressFR'  => 12000.00,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'nom'               => 'Acte de mariage',
                'type_acte'         => 'mariage',
                'sigle'             => 'AM',
                'montantStandardMG'   => 30000.00,
                'montantExpressMG'  => 50000.00,
                'montantStandardFR'   => 40000.00,
                'montantExpressFR'  => 60000.00,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'nom'               => 'Acte de décès',
                'type_acte'         => 'deces',
                'sigle'             => 'AD',
                'montantStandardMG'   => 7000.00,
                'montantExpressMG'  => 9000.00,
                'montantStandardFR'   => 10000.00,
                'montantExpressFR'  => 15000.00,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'nom'               => 'Acte de divorce',
                'type_acte'         => 'divorces',
                'sigle'             => 'ADV',
                'montantStandardMG'   => 60000.00,
                'montantExpressMG'  => 400000.00,
                'montantStandardFR'   => 500000.00,
                'montantExpressFR'  => 70000.00,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('type_actes');
    }
};