<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('label', 120);
            $table->enum('base_calcul', ['H', 'J']);
            $table->string('therapeutic_part_time')->nullable();
        });
        DB::table('absences')->insert([
            ['id' => 1, 'code' => 'AB-100', 'label' => 'Maladie non professionnelle', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 2, 'code' => 'AB-101', 'label' => 'Congé pathologique pré-natal (14 jours)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 3, 'code' => 'AB-102', 'label' => 'Hospitalisation', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 4, 'code' => 'AB-103', 'label' => 'Maladie non professionnelle (IJSSAT/multi-employeur)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 5, 'code' => 'AB-104', 'label' => 'Congé pathologique post-natal', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 6, 'code' => 'AB-105', 'label' => 'Maladie non professionnelle (ALD)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 7, 'code' => 'AB-106', 'label' => 'Longue maladie (secteur public)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 8, 'code' => 'AB-107', 'label' => 'Maladie longue durée (secteur public)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 9, 'code' => 'AB-108', 'label' => 'Femme enceinte dispensée de travail', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 10, 'code' => 'AB-109', 'label' => 'Accident de course', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 11, 'code' => 'AB-110', 'label' => 'Maladie professionnelle', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 12, 'code' => 'AB-111', 'label' => 'Congé pathologique pré-natal (14 jours) sans maintien de salaire', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 13, 'code' => 'AB-120', 'label' => 'Accident de travail', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 14, 'code' => 'AB-130', 'label' => 'Accident de trajet', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 15, 'code' => 'AB-131', 'label' => 'Hospitalisation accident de trajet', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 16, 'code' => 'AB-132', 'label' => 'Hospitalisation AT', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 17, 'code' => 'AB-140', 'label' => 'Temps partiel thérapeutique - obsolète', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 18, 'code' => 'AB-141', 'label' => 'Temps partiel thérapeutique (AT/MP) - obsolète', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 19, 'code' => 'AB-142', 'label' => 'Temps partiel thérapeutique (MNP)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 20, 'code' => 'AB-143', 'label' => 'Temps partiel thérapeutique (AT)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 21, 'code' => 'AB-144', 'label' => 'Temps partiel thérapeutique (ATT)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 22, 'code' => 'AB-145', 'label' => 'Temps partiel thérapeutique (MP)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 23, 'code' => 'AB-150', 'label' => 'Invalidité catégorie 1', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 24, 'code' => 'AB-151', 'label' => 'Invalidité catégorie 2', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 25, 'code' => 'AB-152', 'label' => 'Invalidité catégorie 3', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 26, 'code' => 'AB-153', 'label' => 'Maladie en cours de navigation (MCN)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 27, 'code' => 'AB-154', 'label' => 'Maladie hors navigation (MHN)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 28, 'code' => 'AB-200', 'label' => 'Maternité', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 29, 'code' => 'AB-210', 'label' => 'Paternité', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 30, 'code' => 'AB-220', 'label' => 'Adoption', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 31, 'code' => 'AB-230', 'label' => 'Congé parental', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 32, 'code' => 'AB-240', 'label' => 'Congé de présence parentale', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 33, 'code' => 'AB-250', 'label' => 'Congé de proche aidant', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 34, 'code' => 'AB-251', 'label' => 'Congé de solidarité familiale', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 35, 'code' => 'AB-260', 'label' => 'Absence événement familial (sans retenue)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 36, 'code' => 'AB-261', 'label' => 'Maladie grave conjoint', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 37, 'code' => 'AB-262', 'label' => 'Maladie grave enfant', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 38, 'code' => 'AB-263', 'label' => 'Maladie enfant', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 39, 'code' => 'AB-264', 'label' => 'Congé conventionnel / maintien total', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 40, 'code' => 'AB-265', 'label' => 'Congé conventionnel / maintien partiel', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 41, 'code' => 'AB-270', 'label' => 'Congé de deuil', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 42, 'code' => 'AB-300', 'label' => 'Congés payés', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 43, 'code' => 'AB-301', 'label' => 'Férié chômé', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 44, 'code' => 'AB-310', 'label' => 'RTT', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 45, 'code' => 'AB-320', 'label' => 'Repos compensateur obligatoire', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 46, 'code' => 'AB-330', 'label' => 'Repos compensateur de remplacement', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 47, 'code' => 'AB-335', 'label' => 'Repos compensateur (jour)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 48, 'code' => 'AB-340', 'label' => 'Repos compensateur complémentaire', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 49, 'code' => 'AB-341', 'label' => 'Heures banque', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 50, 'code' => 'AB-350', 'label' => 'Repos forfait jour', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 51, 'code' => 'AB-360', 'label' => 'CP supplémentaires', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 52, 'code' => 'AB-361', 'label' => 'CP supplémentaires 2', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['id' => 53, 'code' => 'AB-362', 'label' => 'Congé supplémentaire apprenti', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 54, 'code' => 'AB-400', 'label' => 'Congé individuel de formation', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 55, 'code' => 'AB-401', 'label' => 'CPF de transition professionnelle', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 56, 'code' => 'AB-410', 'label' => 'Congé formation rémunérée', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 57, 'code' => 'AB-412', 'label' => 'Congé de restructuration', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 58, 'code' => 'AB-413', 'label' => 'Congé pour formation syndicale', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 59, 'code' => 'AB-414', 'label' => 'Congé de reconversion', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 60, 'code' => 'AB-415', 'label' => 'Congé pour VAE', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 61, 'code' => 'AB-416', 'label' => 'Congé pour bilan de compétences', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 62, 'code' => 'AB-417', 'label' => 'Congé de reclassement (au-delà du préavis)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 63, 'code' => 'AB-418', 'label' => 'Congé de mobilité (au-delà du préavis)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 64, 'code' => 'AB-419', 'label' => 'Congé de mobilité (ordonnances Macron)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 65, 'code' => 'AB-420', 'label' => 'Absence formation en alternance', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 66, 'code' => 'AB-430', 'label' => 'Absence formation non rémunérée', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 67, 'code' => 'AB-500', 'label' => 'Solidarité internationale', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 68, 'code' => 'AB-510', 'label' => 'Absence légale autorisée sans retenue', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 69, 'code' => 'AB-520', 'label' => 'Autre absence légale avec retenue', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 70, 'code' => 'AB-530', 'label' => 'Absence pour représentation des salariés', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 71, 'code' => 'AB-531', 'label' => 'Mobilité volontaire sécurisée', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 72, 'code' => 'AB-532', 'label' => 'Congé sabbatique', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 73, 'code' => 'AB-540', 'label' => 'Absence entraînement/compétition sportive', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 74, 'code' => 'AB-600', 'label' => 'Chômage intempéries', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 75, 'code' => 'AB-608', 'label' => 'Activité partielle (réduction du temps)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 76, 'code' => 'AB-609', 'label' => 'Chômage partiel (activité partielle de longue durée)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 77, 'code' => 'AB-610', 'label' => 'Activité partielle (suspension intégrale)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 78, 'code' => 'AB-611', 'label' => 'Chômage partiel/congés payés', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 79, 'code' => 'AB-612', 'label' => 'Activité partielle/formation', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 80, 'code' => 'AB-613', 'label' => 'Activité partielle LD (réduction du temps)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 81, 'code' => 'AB-614', 'label' => 'Activité partielle LD (suspension temporaire)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 82, 'code' => 'AB-620', 'label' => 'Absence non rémunérée (autorisée)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 83, 'code' => 'AB-630', 'label' => 'Absence non rémunérée (non autorisée)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 84, 'code' => 'AB-631', 'label' => 'Absence rémunérée', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 85, 'code' => 'AB-632', 'label' => 'Congé sans solde', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 86, 'code' => 'AB-633', 'label' => 'Annulation absence', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 87, 'code' => 'AB-634', 'label' => 'Férié chômé non payé', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 88, 'code' => 'AB-635', 'label' => 'Cessation concertée de travail (grève)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 89, 'code' => 'AB-640', 'label' => 'Mise à pied disciplinaire', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 90, 'code' => 'AB-641', 'label' => 'Mise à pied conservatoire', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 91, 'code' => 'AB-642', 'label' => 'Détention provisoire', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 92, 'code' => 'AB-650', 'label' => 'Préavis non effectué', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 93, 'code' => 'AB-651', 'label' => 'Préavis non effectué payé', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 94, 'code' => 'AB-652', 'label' => 'Préavis non effectué payé (congé de reclassement)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 95, 'code' => 'AB-653', 'label' => 'Préavis non effectué payé (congé de mobilité)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 96, 'code' => 'AB-660', 'label' => 'Période non travaillée (CDI intermittent sans lissage)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 97, 'code' => 'AB-670', 'label' => 'Préretraite d\'entreprise (sans rupture de contrat)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 98, 'code' => 'AB-680', 'label' => 'Détachement (établissement d\'origine)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 99, 'code' => 'AB-685', 'label' => 'Congé pour création ou reprise d\'entreprise', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 100, 'code' => 'AB-686', 'label' => 'Suspension pour expatriation', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 101, 'code' => 'AB-690', 'label' => 'Activité partielle (réduction du temps) - Impossible de calculer', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['id' => 102, 'code' => 'AB-691', 'label' => 'Activité partielle (suspension intégrale) - Impossible de calculer', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
