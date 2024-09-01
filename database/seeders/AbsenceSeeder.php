<?php

namespace Database\Seeders;

use App\Models\Absences\Absence;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AbsenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $absences = [
            ['code' => 'AB-100', 'label' => 'Maladie non professionnelle', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-101', 'label' => 'Congé pathologique pré-natal (14 jours)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-102', 'label' => 'Hospitalisation', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-103', 'label' => 'Maladie non professionnelle (IJSSAT/multi-employeur)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-104', 'label' => 'Congé pathologique post-natal', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-105', 'label' => 'Maladie non professionnelle (ALD)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-106', 'label' => 'Longue maladie (secteur public)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-107', 'label' => 'Maladie longue durée (secteur public)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-108', 'label' => 'Femme enceinte dispensée de travail', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-109', 'label' => 'Accident de course', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-110', 'label' => 'Maladie professionnelle', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-111', 'label' => 'Congé pathologique pré-natal (14 jours) sans maintien de salaire', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-120', 'label' => 'Accident de travail', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-130', 'label' => 'Accident de trajet', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-131', 'label' => 'Hospitalisation accident de trajet', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-132', 'label' => 'Hospitalisation AT', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-140', 'label' => 'Temps partiel thérapeutique - obsolète', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-141', 'label' => 'Temps partiel thérapeutique (AT/MP) - obsolète', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-142', 'label' => 'Temps partiel thérapeutique (MNP)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-143', 'label' => 'Temps partiel thérapeutique (AT)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-144', 'label' => 'Temps partiel thérapeutique (ATT)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-145', 'label' => 'Temps partiel thérapeutique (MP)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-150', 'label' => 'Invalidité catégorie 1', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-151', 'label' => 'Invalidité catégorie 2', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-152', 'label' => 'Invalidité catégorie 3', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-153', 'label' => 'Maladie en cours de navigation (MCN)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-154', 'label' => 'Maladie hors navigation (MHN)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-200', 'label' => 'Maternité', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-210', 'label' => 'Paternité', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-220', 'label' => 'Adoption', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-230', 'label' => 'Congé parental', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-240', 'label' => 'Congé de présence parentale', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-250', 'label' => 'Congé de proche aidant', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-251', 'label' => 'Congé de solidarité familiale', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-260', 'label' => 'Absence événement familial (sans retenue)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-261', 'label' => 'Maladie grave conjoint', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-262', 'label' => 'Maladie grave enfant', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-263', 'label' => 'Maladie enfant', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-264', 'label' => 'Congé conventionnel / maintien total', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-265', 'label' => 'Congé conventionnel / maintien partiel', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-270', 'label' => 'Congé de deuil', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-300', 'label' => 'Congés payés', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-301', 'label' => 'Férié chômé', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-310', 'label' => 'RTT', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-320', 'label' => 'Repos compensateur obligatoire', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-330', 'label' => 'Repos compensateur de remplacement', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-335', 'label' => 'Repos compensateur (jour)', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-340', 'label' => 'Repos compensateur complémentaire', 'base_calcul' => 'J', 'therapeutic_part_time' => null],
            ['code' => 'AB-341', 'label' => 'Heures banque', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-350', 'label' => 'Repos forfait jour', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-360', 'label' => 'Heures supplémentaires', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-370', 'label' => 'Heures complémentaires', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-380', 'label' => 'Hors contrat', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-390', 'label' => 'Mission', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-400', 'label' => 'Formation', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-410', 'label' => 'Stage', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-420', 'label' => 'Congés exceptionnels', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-430', 'label' => 'Congés de maternité (sans maintien de salaire)', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-440', 'label' => 'Congés de paternité', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-450', 'label' => 'Congés de soutien familial', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-460', 'label' => 'Congés d’adoption', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-470', 'label' => 'Congés de présence parentale', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-480', 'label' => 'Congés de proche aidant', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-490', 'label' => 'Congés de solidarité familiale', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-500', 'label' => 'Congés de bilan de compétences', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-510', 'label' => 'Congés de recherche d’emploi', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-520', 'label' => 'Congés de formation professionnelle', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-530', 'label' => 'Congés pour création d’entreprise', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-540', 'label' => 'Congés sabbatiques', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-550', 'label' => 'Congés d’éducation pour enfant handicapé', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-560', 'label' => 'Congés de formation syndicale', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-570', 'label' => 'Congés pour événement familial', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-580', 'label' => 'Congés de maladie professionnelle', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-590', 'label' => 'Congés d’accident de travail', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-600', 'label' => 'Congés d’accident de trajet', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-610', 'label' => 'Congés de maladie', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-620', 'label' => 'Congés pour invalidité', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-630', 'label' => 'Congés de maladie en cours de navigation', 'base_calcul' => 'H', 'therapeutic_part_time' => null],
            ['code' => 'AB-640', 'label' => 'Congés de maladie hors navigation', 'base_calcul' => 'H', 'therapeutic_part_time' => null]
        ];

        foreach ($absences as $absence){
            Absence::create($absence);
        }

    }
}
