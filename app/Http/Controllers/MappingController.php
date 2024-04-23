<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Mapping;
use Illuminate\Http\Request;
use League\Csv\CharsetConverter;
use League\Csv\Reader;
use Illuminate\Support\Collection;

class MappingController extends Controller
{
    public function setMapping(Request $request)
    {
        // Initialisation de l'encodage et du formatage
        $encoder = (new CharsetConverter())->inputEncoding('utf-8');
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
            $unmatched_rubriques = []; // Stockage des rubriques sans correspondance

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

                    // Rechercher tous les mappings correspondants dans la base de données
                    $mappings = Mapping::where('input_rubrique', $input_rubrique)->get();

                    // Si au moins un mapping est trouvé
                    if ($mappings->isNotEmpty()) {
                        foreach ($mappings as $mapping) {
                            // Utiliser la relation output pour obtenir les détails de la rubrique de sortie associée
                            $output = $mapping->output;
                            $results[] = [
                                'input_rubrique' => $input_rubrique,
                                'type_rubrique' => $mapping->name_rubrique,
                                'output_rubrique' => $output->code,
                                'base_calcul' => $output->base_calcul,
                                'label' => $output->label
                            ];
                        }
                    } else {
                        // Si aucun mapping n'est trouvé, stocker la rubrique sans correspondance
                        $unmatched_rubriques[] = [
                            'input_rubrique' => $input_rubrique,
                            'type_rubrique' => 'A définir',
                            'output_rubrique' => 'A définir',
                            'base_calcul' => 'A définir',
                            'label' => 'A définir',
                        ];
                    }
                }
            }

            // Retourner les résultats des rubriques avec correspondance et sans correspondance
            $response = [
                'correspondances' => $results,
                'sans_correspondance' => $unmatched_rubriques
            ];

            return response()->json($response);
        }

        return response()->json('Aucun fichier importé');
    }

}


