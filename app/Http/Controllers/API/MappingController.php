<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mapping;
use App\Models\MappingNew;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use League\Csv\CharsetConverter;
use League\Csv\Reader;
use App\Http\Controllers\API\ApiAuthController;

class MappingController extends Controller
{
    public function getMapping(Request $request)
    {
        $companyFolder = $request->get('company_folder_id');

        if (!$companyFolder) {
            return response()->json("L'id du dossier est requis", 400);
        }

        // Initialisation de l'encodage et du formatage
        $encoder = (new CharsetConverter())->inputEncoding('utf-8');

        // Vérifier si le fichier CSV est présent dans la requête
        if ($request->hasFile('csv')) {
            $file = $request->file('csv');
            $reader = Reader::createFromPath($file->getPathname(), 'r');
            $reader->addFormatter($encoder);
            $reader->setDelimiter(';');

            $records = $reader->getRecords();
            $results = [];
            $processed_records = new Collection();
            $unmatched_rubriques = [];

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
                    $mappings = Mapping::with('folder')
                        ->where('company_folder_id', $companyFolder)
                        ->get();

                    $is_mapped = false;

                    foreach ($mappings as $mapping) {
                        // Décoder les données JSON
                        $mappingDataArray = $mapping->data;
                        if (is_array($mappingDataArray)) {
                            foreach ($mappingDataArray as $data) {
                                if (isset($data['input_rubrique']) && $data['input_rubrique'] === $input_rubrique) {
                                    $outputType = $data['output_type'];
                                    $outputRubriqueId = $data['output_rubrique_id'];

                                    // Résolvez le nom de classe du modèle à utiliser
                                    $outputModelClass = App::make($outputType);

                                    // Créez une instance du modèle correspondant
                                    $outputModelInstance = new $outputModelClass;

                                    // Recherchez l'objet OutputRubrique correspondant
                                    $output = $outputModelInstance->findOrFail($outputRubriqueId);
                                    $results[] = [
                                        'input_rubrique' => $data['input_rubrique'],
                                        'type_rubrique' => $data['name_rubrique'] ?? null, // Utilisez $data['name_rubrique'] s'il est présent dans les données
                                        'output_rubrique' => $output->code,
                                        'base_calcul' => $output->base_calcul,
                                        'label' => $output->label,
                                        'is_mapped' => true,
                                        'company_folder_id' => $companyFolder,
                                    ];
                                    $is_mapped = true;
                                    break 2; // Break out of both foreach loops
                                }
                            }
                        }
                    }

                    if (!$is_mapped) {
                        $unmatched_rubriques[] = [
                            'input_rubrique' => $input_rubrique,
                            'type_rubrique' => null,
                            'output_rubrique' => null,
                            'base_calcul' => null,
                            'label' => null,
                            'is_mapped' => false,
                            'company_folder_id' => $companyFolder,
                        ];
                    }
                }
            }

            $rubrique_merged = array_merge($results, $unmatched_rubriques);
            return response()->json($rubrique_merged);
        }

        return response()->json('Aucun fichier importé');
    }

    public function updateMapping(Request $request, $id)
    {
        $mapping = Mapping::findOrFail($id);
        $validatedData = $request->validate([
            'input_rubrique' => 'required|string|regex:/^\d{1,3}[A-Z]{0,2}$/',
            'name_rubrique' => 'required|string|max:255',
            'output_rubrique_id' => 'required|integer',
            'company_folder_id' => 'required|integer',
            'output_type' => 'required|string',
        ]);

        if ($mapping->update($validatedData)) {
            return response()->json(['message' => 'OK']);
        }
        return response()->json(['message' => 'PAS OK']);
    }

    public function storeMapping(Request $request)
    {
        // Validation des données entrantes
        $validatedData = $request->validate([
            'input_rubrique' => 'required|string|regex:/^\d{1,3}[A-Z]{0,2}$/',
            'name_rubrique' => 'required|string|max:255',
            'output_rubrique_id' => 'required|integer',
            'output_type' => 'required|string',
        ]);


        $companyFolder = $request->get('company_folder_id');

        // Vérifier s'il existe déjà un mapping avec la même `input_rubrique` et le même `output_type`
        $existingMapping = Mapping::with('folder')->where('company_folder_id', $companyFolder)->get();

        foreach ($existingMapping as $mapping) {
            foreach ($mapping->data as $data) {
                $outputType = $data['output_type'];
                $outputRubriqueId = $data['output_rubrique_id'];

                // Résolvez le nom de classe du modèle à utiliser
                $outputModelClass = App::make($outputType);

                // Créez une instance du modèle correspondant
                $outputModelInstance = new $outputModelClass;

                // Recherchez l'objet OutputRubrique correspondant
                $output = $outputModelInstance->findOrFail($outputRubriqueId);
                if ($data['input_rubrique'] === $validatedData['input_rubrique']) {
                    return response()->json([
                        'error' => 'La rubrique ' . $validatedData['input_rubrique'] . ' est déjà associée à ' . $output->code,
                    ], 409);
                }
                $newMapping = [
                    'input_rubrique' => $validatedData['input_rubrique'],
                    'name_rubrique' => $validatedData['name_rubrique'],
                    'output_rubrique_id' => $validatedData['output_rubrique_id'],
                    'output_type' => $validatedData['output_type'],
                ];

                $data[] = $newMapping;

                // Convertir les mappings en chaîne JSON et les mettre à jour dans la base de données
                $output->update(['data' => $data]);
            }
            return response()->json(['success' => 'Mapping ajouté avec succès'], 201);
        }
        
        // Vérification de l'existence de la rubrique dans la table appropriée
        $outputClass = $validatedData['output_type']; // La classe associée à `output_type`
        if (!class_exists($outputClass)) {
            return response()->json([
                'error' => 'Le type spécifié n\'existe pas: ' . $outputClass,
            ], 404);
        }

        $outputRubrique = $outputClass::find($validatedData['output_rubrique_id']);

        if (!$outputRubrique) {
            return response()->json([
                'error' => 'La rubrique spécifiée dans la table ' . $outputClass . ' n\'existe pas.',
                'rubrique_code' => $validatedData['output_rubrique_id'],
                'suggestion' => 'Voulez-vous créer cette rubrique ?'
            ], 404);
        }


        // Créer un nouveau mappage dans la base de données
        $mappingData[] = [
            'input_rubrique' => $validatedData['input_rubrique'],
            'name_rubrique' => $validatedData['name_rubrique'],
            'output_rubrique_id' => $validatedData['output_rubrique_id'],
            'output_type' => $validatedData['output_type'],
        ];

        // Convertir les mappings en chaîne JSON et les stocker dans la base de données
        if (Mapping::create([
            'company_folder_id' => $validatedData['company_folder_id'],
            'data' => $mappingData,
        ])) {
            return response()->json(['success' => 'Mappage ajouté avec succès'], 201);
        } else {
            return response()->json(['error' => 'Erreur lors de l\'ajout du mappage'], 500);
        }
    }
}
