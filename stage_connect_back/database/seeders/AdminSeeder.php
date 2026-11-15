<?php
// database/seeders/AdminUserSeeder.php



namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Annee;
use App\Models\Domaine;
use App\Models\Ville;
use App\Models\Etablissement;
use App\Models\Filiere;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // 1. Données des Années (issu de AnneeSeeder.php)
        $annees = [
            'L1', 'L2', 'L3', 'M1', 'M2', 'Doctorat', 'Année Préparatoire',
        ];
        foreach ($annees as $libannee) {
            Annee::firstOrCreate(['libannee' => $libannee]);
        }
        $this->command->info('Années seeded!');

        // 2. Données des Domaines (issu de DomainesTableSeeder.php)
        $domaines = [
            "Technologies de l'Information et de la Communication (TIC)", "Santé et Pharmaceutique", "Éducation et Formation", "Finance et Assurances",
            "Agriculture et Agro-industrie", "Commerce et Distribution", "Tourisme et Hôtellerie", "Bâtiment et Travaux Publics (BTP)",
            "Transport et Logistique", "Énergie et Environnement", "Mines et Carrières", "Médias et Communication",
            "Artisanat et Production locale", "Services aux entreprises", "Conseil et Audit", "Industrie manufacturière",
            "Télécommunications", "Banque", "Microfinance", "Organisations Non Gouvernementales (ONG)",
            "Administration Publique", "Recherche et Développement", "Design et Création", "Événementiel et Loisirs",
            "Sécurité et Défense", "Immobilier", "Automobile", "Textile et Habillement", "Informatique / Services Numériques",
            "Alimentation et Boissons", "Mode et Beauté"
        ];
        foreach ($domaines as $libdomaine) {
            Domaine::firstOrCreate(['libdomaine' => $libdomaine]);
        }
        $this->command->info('Domaines seeded!');

        // 3. Données des Villes (issu de VillesTableSeeder.php)
        $villes = [
            'Cotonou', 'Abomey-Calavi', 'Ouidah', 'Allada', 'Toffo', 'Zè', 'Porto-Novo', 'Sèmè-Kpodji', 'Adjarra', 'Aguégués',
            'Akpro-Missérété', 'Avrankou', 'Bonou', 'Dangbo', 'Ekpè', 'Ifangni', 'Missérété', 'Ouèssè', 'Kétou', 'Pobè',
            'Adja-Ouèrè', 'Ifangni', 'Sakété', 'Djougou', 'Bassila', 'Copargo', 'Ouaké', 'Parakou', 'N\'Dali', 'Pèrèrè',
            'Sinendé', 'Tchaourou', 'Bembèrèkè', 'Kalalé', 'Nikki', 'Natitingou', 'Tanguiéta', 'Banikoara', 'Kandi', 'Gogounou',
            'Karimama', 'Malanville', 'Ségbana', 'Dogbo', 'Aplahoué', 'Djakotomey', 'Klhouékanmey', 'Lalo', 'Toviklin', 'Lokossa',
            'Athiémé', 'Bopa', 'Comé', 'Grand-Popo', 'Houéyogbé', 'Abomey', 'Bohicon', 'Covè', 'Djidja', 'Ouinhi', 'Za-Kpota',
            'Zogbodomey', 'Save', 'Savalou', 'Dassa-Zoumè', 'Glazoué', 'Ouèssè', 'Bantè', 'Boukoumbé', 'Cobly', 'Kérou',
            'Kouandé', 'Pehonko', 'Toukountouna', 'Agbangnizoun', 'Banamè', 'Zagnanado', 'Come', 'Kpomasse', 'Tori-Bossito'
        ];
        foreach ($villes as $libville) {
            Ville::firstOrCreate(['nom_ville' => $libville]);
        }
        $this->command->info('Villes seeded!');

        
       

        // 4. Données des Universités privées reconnues
        $universities = [

            'ENEAM(Ecole Nationale d’Economie Appliquée et de Management)',
            'UPI-ONM (Université Polytechnique Internationale Obiang Nguema Mbasogo)',
            'Les Cours Sonou',
            'HECM (Haute École de Commerce et de Management)',
            'ESGIS (École Supérieure de Gestion d’Informatique et des Sciences)',
            'ESTAM (École Supérieure des Technologies Avancées et de Management)',
            'ESCAE (École Supérieure des Sciences, de Commerce et d’Administration des Entreprises)',
            'ISCG (Institut Supérieur de Communication et de Gestion)',
            'IUB (Institut Universitaire du Bénin)',
            'ESTB (École Supérieure de Technologie et de Business)',
            'ESAE (École Supérieure d’Administration d’Économie, de Journalisme et des Métiers de l’Audiovisuel)',
            'UATM GASA Formation',
            'Université PIGIER Bénin',
            'ECOTES (École de Commerce et de Technologie Supérieure)',
            'HNAUB (Houdegbe North American University of Benin)',
            'INEAM (Institut National Économique et Administratif du Management)',
            'ISM Adonai',
            'EPAC Formation',
            'CESAG Bénin',
            'UPESB (Université Privée des Études Sociales du Bénin)',
            'EPAC (École Polytechnique d’Abomey-Calavi)',
    'ENI (École Normale d’Instituteurs)',
    'ENS (École Normale Supérieure)',
    'ESTIC (École des Sciences et Techniques de l’Information et de la Communication)',
    'ISBA (Institut Supérieur des Beaux-Arts)',
    'INMeS (Institut National Médico-Sanitaire)',
    'INSPEI (Institut National Supérieur de Préparation aux Études d’Ingénierie)',
    'ENSET (École Normale Supérieure de l’Enseignement Technique de Lokossa)',
    'FAST (Faculté des Sciences et Techniques de Natitingou)',
    'INFAS (Institut de Formation aux Activités de Santé)',
    'INFSS (Institut National de Formation des Soins de Santé)',
    'IFRIS (Institut de Formation en Recherche et Innovation en Santé)',
    'ESA (École des Sciences et Techniques de Production Végétale)',
    'ESAE (École Supérieure d’Agronomie et d’Élevage)',
    'ESDER (École Supérieure de Développement Rural)',
    'ESPA (École Supérieure de Pêche et d’Aquaculture)',
    'École de Foresterie d’Adjohoun',
    'École de Génie Rural de Kétou',
    'École d’Agribusiness de Porto-Novo',
    'École de la Douane',
    'ENAM (École Nationale d’Administration et de Magistrature)',
    'ENP (École Nationale de Police)',
    'EOFAB (École des Officiers des Forces Armées Béninoises)',
    'École Nationale des Eaux, Forêts et Chasse de Kandi',
    'École des Travaux Publics de Djougou',
    'École Normale de Sport de Natitingou'
        ];

        foreach ($universities as $name) {
            Etablissement::firstOrCreate(
                ['nom_etablissement' => $name],
                
            );
        }
        $this->command->info('Universités seeded!');

        // 5. Données des Filières les plus courantes au Bénin
        $filiereNames = [
            'Informatique',
    'Génie logiciel',
    'Génie électrique',
    'Génie civil',
    'Électronique',
    'Électrotechnique',
    'Réseaux et télécommunications',
    'Mathématiques appliquées',
    'Physique',
    'Chimie',
    'Biologie',
    'Énergies renouvelables',
    'Robotique et domotique',

    // Filières en santé et paramédical
    'Médecine générale',
    'Pharmacie',
    'Odontologie (chirurgie dentaire)',
    'Sage-femme',
    'Soins infirmiers',
    'Biologie médicale',
    'Santé publique',
    'Nutrition et diététique',

    // Filières en économie, gestion et commerce
    'Économie appliquée',
    'Comptabilité',
    'Finance',
    'Audit et contrôle de gestion',
    'Marketing',
    'Banque et microfinance',
    'Management des organisations',
    'Gestion des ressources humaines',
    'Logistique et transport',

    // Filières en droit et sciences politiques
    'Droit public',
    'Droit privé',
    'Droit des affaires',
    'Droit international',
    'Science politique',
    'Relations internationales',

    // Filières en lettres, langues et communication
    'Lettres modernes',
    'Langues étrangères appliquées (Anglais, Espagnol)',
    'Journalisme',
    'Communication des organisations',
    'Audiovisuel et multimédia',
    'Traduction et interprétariat',

    // Filières en sciences sociales et humaines
    'Sociologie',
    'Psychologie',
    'Histoire et géographie',
    'Philosophie',
    'Anthropologie',
    'Éducation et pédagogie',
    'Économie sociale et solidaire',

    // Filières agricoles et environnementales
    'Agronomie générale',
    'Agroéconomie',
    'Agrobusiness',
    'Horticulture',
    'Foresterie',
    'Aquaculture',
    'Élevage',
    'Génie rural',
    'Protection de l’environnement',
    'Sciences du sol',
    'Nutrition animale',

    // Filières techniques et industrielles
    'Mécanique industrielle',
    'Travaux publics',
    'Topographie',
    'Urbanisme et aménagement',
    'Bâtiment et construction',
    'Design industriel',
    'Électronique embarquée',

    // Filières artistiques
    'Beaux-arts',
    'Design graphique',
    'Architecture d’intérieur',
    'Musique',
    'Théâtre et arts dramatiques',

    // Filières de l’enseignement
    'Formation des instituteurs',
    'Formation des enseignants du secondaire',
    'Formation en éducation spécialisée',
    'Sciences de l’éducation'
        ];

        foreach ($filiereNames as $libfil) {
            Filiere::firstOrCreate(['libfil' => $libfil]);
        }
        $this->command->info('Filières seeded!');
    }
}
