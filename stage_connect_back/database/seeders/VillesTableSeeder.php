<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Ville;

class VillesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $villes = [
            'Cotonou',
            'Abomey-Calavi',
            'Ouidah',
            'Allada',
            'Toffo',
            'Zè',
            'Porto-Novo',
            'Sèmè-Kpodji',
            'Adjarra',
            'Aguégués',
            'Akpro-Missérété',
            'Avrankou',
            'Bonou',
            'Dangbo',
            'Ekpè',
            'Ifangni',
            'Missérété',
            'Ouèssè',
            'Kétou',
            'Pobè',
            'Adja-Ouèrè',
            'Ifangni',
            'Sakété',
            'Djougou',
            'Bassila',
            'Copargo',
            'Ouaké',
            'Parakou',
            'N\'Dali',
            'Pèrèrè',
            'Sinendé',
            'Tchaourou',
            'Bembèrèkè',
            'Kalalé',
            'Nikki',
            'Natitingou',
            'Tanguiéta',
            'Banikoara',
            'Kandi',
            'Gogounou',
            'Karimama',
            'Malanville',
            'Ségbana',
            'Dogbo',
            'Aplahoué',
            'Djakotomey',
            'Klhouékanmey',
            'Lalo',
            'Toviklin',
            'Lokossa',
            'Athiémé',
            'Bopa',
            'Comé',
            'Grand-Popo',
            'Houéyogbé',
            'Abomey',
            'Bohicon',
            'Covè',
            'Djidja',
            'Ouinhi',
            'Za-Kpota',
            'Zogbodomey',
            'Save',
            'Savalou',
            'Dassa-Zoumè',
            'Glazoué',
            'Ouèssè',
            'Bantè',
            'Boukoumbé',
            'Cobly',
            'Kérou',
            'Kouandé',
            'Pehonko',
            'Toukountouna',
            'Agbangnizoun',
            'Banamè',
            'Zagnanado',
            'Come',
            'Kpomasse',
            'Tori-Bossito'
        ];

        foreach ($villes as $nom_ville) {
            
            Ville::firstOrCreate(['nom_ville' => $nom_ville]);
        }
    }
}

//php artisan db:seed --class=VillesTableSeeder