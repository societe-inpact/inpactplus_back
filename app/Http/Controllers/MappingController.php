<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Mapping;
use Illuminate\Http\Request;
use League\Csv\CharsetConverter;
use League\Csv\Reader;
use \Illuminate\Support\Collection;
class MappingController extends Controller
{
    public function setMapping(Request $request)
    {
        // Initialisation de l'encodage et du formatage
        $encoder = (new CharsetConverter())->inputEncoding('iso-8859-15');
        $formatter = fn(array $row): array => array_map('strtoupper', $row);

        // Vérifier si le fichier CSV est présent dans la requête
        if ($request->hasFile('csv')) {
            // Récupérer le fichier CSV
            $file = $request->file('csv');
            // Créer un lecteur CSV à partir du fichier
            $reader = Reader::createFromPath($file->getPathname(), 'r');
            // Configurer le lecteur
            $reader->addFormatter($encoder);
            $reader->setDelimiter(';');
            $reader->addFormatter($formatter);

            // Obtenir les enregistrements du CSV
            $records = $reader->getRecords();
            $results = [];
            $processed_records = new Collection(); // Ensemble pour éviter les doublons

            // Expression régulière pour la rubrique d'entrée
            $rubrique_regex = '/^\d{1,3}[A-Z]{0,2}$/';

            // Parcourir chaque enregistrement
            foreach ($records as $record) {
                $input_rubrique = null;

                // Parcourir chaque valeur de l'enregistrement
                foreach ($record as $value) {
                    // Vérifier si la valeur correspond à la regex de la rubrique
                    if (preg_match($rubrique_regex, $value)) {
                        $input_rubrique = $value;
                        break; // Arrêter la boucle une fois la rubrique d'entrée trouvée
                    }
                }

                // Si la rubrique d'entrée a été trouvée et n'a pas déjà été traitée
                if ($input_rubrique !== null && !$processed_records->contains($input_rubrique)) {
                    // Ajouter la rubrique traitée à l'ensemble
                    $processed_records->push($input_rubrique);

                    // Rechercher le mapping correspondant dans la base de données
                    $mapping = Mapping::where('input_rubrique', $input_rubrique)->first();

                    // Vérifier si un mapping existe
                    if ($mapping) {
                        $absence = Absence::findOrFail($mapping->output_rubrique);
                        $results[] = [
                            'label' => $absence->label,
                            'Rubrique d\'entrée' => $input_rubrique,
                            'Rubrique de sortie' => $absence->code
                        ];
                    } else {
                        // Si aucun mapping n'est trouvé
                        echo "Aucune correspondance trouvée pour la rubrique d'entrée : $input_rubrique\n";
                    }
                }
            }
            echo "\nCorrespondances trouvées\n";
            return response()->json($results);
        }

        return response()->json('Aucun fichier importé');
    }
}

