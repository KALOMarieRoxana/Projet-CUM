// database/migrations/2024_01_01_000001_create_type_actes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeActesTable extends Migration
{
    public function up()
    {
        Schema::create('type_actes', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Acte de naissance, mariage, etc.
            $table->string('type_acte')->unique(); // naissance, mariage, deces, divorce
            $table->decimal('prix_standard', 10, 2)->default(5000);
            $table->decimal('prix_express', 10, 2)->default(10000);
            $table->timestamps();
        });

        // Insertion des types d'actes par défaut
        DB::table('type_actes')->insert([
            ['nom' => 'Acte de naissance', 'type_acte' => 'naissance', 'prix_standard' => 5000, 'prix_express' => 10000, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Acte de mariage', 'type_acte' => 'mariage', 'prix_standard' => 7000, 'prix_express' => 12000, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Acte de décès', 'type_acte' => 'deces', 'prix_standard' => 5000, 'prix_express' => 10000, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Acte de divorce', 'type_acte' => 'divorce', 'prix_standard' => 8000, 'prix_express' => 15000, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('type_actes');
    }
}
