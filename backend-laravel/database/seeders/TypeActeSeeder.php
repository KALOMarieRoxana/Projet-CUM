<?php

namespace Database\Seeders;

use App\Models\TypeActe;
use Illuminate\Database\Seeder;

class TypeActeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'nom' => 'Acte de naissance',
                'type_acte' => 'naissance',
                'sigle' => 'AN',
                'montantStandardMG' => 2500,
                'montantExpressMG' => 5000,
                'montantStandardFR' => 3500,
                'montantExpressFR' => 7000,
            ],
            [
                'nom' => 'Acte de mariage',
                'type_acte' => 'mariage',
                'sigle' => 'AM',
                'montantStandardMG' => 4000,
                'montantExpressMG' => 8000,
                'montantStandardFR' => 5500,
                'montantExpressFR' => 11000,
            ],
            [
                'nom' => 'Acte de décès',
                'type_acte' => 'deces',
                'sigle' => 'AD',
                'montantStandardMG' => 2000,
                'montantExpressMG' => 4000,
                'montantStandardFR' => 3000,
                'montantExpressFR' => 6000,
            ],
            [
                'nom' => 'Acte de divorce',
                'type_acte' => 'divorces',
                'sigle' => 'AV',
                'montantStandardMG' => 5000,
                'montantExpressMG' => 10000,
                'montantStandardFR' => 7000,
                'montantExpressFR' => 14000,
            ],
        ];

        foreach ($types as $type) {
            TypeActe::updateOrCreate(
                ['type_acte' => $type['type_acte']],
                $type
            );
        }
    }
}