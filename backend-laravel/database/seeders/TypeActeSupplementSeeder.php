<?php

namespace Database\Seeders;

use App\Models\TypeActe;
use App\Models\TypeActeSupplement;
use Illuminate\Database\Seeder;

class TypeActeSupplementSeeder extends Seeder
{
    public function run()
    {
        // Récupérer le type d'acte "naissance"
        $naissance = TypeActe::where('type_acte', 'naissance')->first();
        
        if ($naissance) {
            $supplements = [
                [
                    'nom' => 'Acte de naissance (copie intégrale)',
                    'code' => 'acte_naissance_integrale',
                    'description' => 'Copie intégrale de l\'acte de naissance',
                    'prix_standard_fr' => 5000,
                    'prix_express_fr' => 10000,
                    'prix_standard_mg' => 3000,
                    'prix_express_mg' => 6000,
                    'ordre' => 1,
                ],
                [
                    'nom' => 'Bulletin de naissance',
                    'code' => 'bulletin_naissance',
                    'description' => 'Bulletin de naissance (résumé)',
                    'prix_standard_fr' => 3000,
                    'prix_express_fr' => 6000,
                    'prix_standard_mg' => 2000,
                    'prix_express_mg' => 4000,
                    'ordre' => 2,
                ],
                [
                    'nom' => 'Extrait d\'acte de naissance',
                    'code' => 'extrait_naissance',
                    'description' => 'Extrait d\'acte de naissance',
                    'prix_standard_fr' => 2000,
                    'prix_express_fr' => 4000,
                    'prix_standard_mg' => 1500,
                    'prix_express_mg' => 3000,
                    'ordre' => 3,
                ],
                [
                    'nom' => 'Copie littérale',
                    'code' => 'copie_litterale_naissance',
                    'description' => 'Copie littérale avec toutes les mentions',
                    'prix_standard_fr' => 7000,
                    'prix_express_fr' => 14000,
                    'prix_standard_mg' => 5000,
                    'prix_express_mg' => 10000,
                    'ordre' => 4,
                ],
            ];

            foreach ($supplements as $supp) {
                TypeActeSupplement::create(array_merge($supp, [
                    'type_acte_id' => $naissance->id,
                ]));
            }
        }

        // Ajouter aussi pour mariage
        $mariage = TypeActe::where('type_acte', 'mariage')->first();
        if ($mariage) {
            TypeActeSupplement::create([
                'type_acte_id' => $mariage->id,
                'nom' => 'Copie intégrale de mariage',
                'code' => 'copie_integrale_mariage',
                'prix_standard_fr' => 7000,
                'prix_express_fr' => 14000,
                'prix_standard_mg' => 5000,
                'prix_express_mg' => 10000,
                'ordre' => 1,
            ]);

            TypeActeSupplement::create([
                'type_acte_id' => $mariage->id,
                'nom' => 'Certificat de mariage',
                'code' => 'certificat_mariage',
                'prix_standard_fr' => 5000,
                'prix_express_fr' => 10000,
                'prix_standard_mg' => 3000,
                'prix_express_mg' => 6000,
                'ordre' => 2,
            ]);
        }
    }
}