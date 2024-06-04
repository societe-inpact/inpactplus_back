<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use League\Csv\CharsetConverter;
use League\Csv\Reader;

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
                    $mappings = Mapping::with('folder')->where('input_rubrique', $input_rubrique)->where('company_folder_id', $companyFolder)->get();

                    // Si au moins un mapping est trouvé
                    if ($mappings->isNotEmpty()) {
                        foreach ($mappings as $mapping) {
                            // Utiliser la relation output pour obtenir les détails de la rubrique de sortie associée
                            $output = $mapping->output;
                            $results[] = [
                                'id' => $mapping->id,
                                'input_rubrique' => $input_rubrique,
                                'type_rubrique' => $mapping->name_rubrique,
                                'output_rubrique' => $output->code,
                                'base_calcul' => $output->base_calcul,
                                'label' => $output->label,
                                'is_mapped' => true,
                                'company_folder_id' => $companyFolder,
                            ];
                        }
                    } else {
                        // Si aucun mapping n'est trouvé, stocker la rubrique sans correspondance
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

    public function setMapping(Request $request)
    {
        // Validation des données entrantes
        $validatedData = $request->validate([
            'input_rubrique' => 'required|string|regex:/^\d{1,3}[A-Z]{0,2}$/',
            'name_rubrique' => 'required|string|max:255',
            'output_rubrique_id' => 'required|integer',
            'company_folder_id' => 'required|integer',
            'output_type' => 'required|string',
        ]);

        // Vérifier s'il existe déjà un mapping avec la même `input_rubrique` et le même `output_type`
        $existingMapping = Mapping::where('input_rubrique', $validatedData['input_rubrique'])
            ->where('output_type', $validatedData['output_type'])
            ->where('company_folder_id', $validatedData['company_folder_id'])
            ->first();

        if ($existingMapping) {
            // Si l'association existe et que l'`output_rubrique_id` est différent, renvoyer une erreur

            if ($existingMapping->output_rubrique_id !== $validatedData['output_rubrique_id']) {
                $output = $existingMapping->output;

                // Utilisez le tableau de mappage pour obtenir le nom lisible de la table
                $tableName = $tableNames[$existingMapping->output_type] ?? $existingMapping->output_type;

                return response()->json([
                    'error' => 'La rubrique ' . $existingMapping->input_rubrique . ' est déjà associée à ' . $output->code,
                ], 409);
            } else {
                return response()->json([
                    'error' => 'La rubrique d\'entrée est déjà associée à ce code et à ce type.',
                ], 409);
            }
        } else {
            // Si aucun mapping n'est trouvé pour `input_rubrique` et `output_type`, créer un nouveau mapping

            // Vérification de l'existence de la rubrique dans la table appropriée
            $outputClass = $validatedData['output_type']; // La classe associée à `output_type`
            $outputRubrique = $outputClass::find($validatedData['output_rubrique_id']);

            if (!$outputRubrique) {
                return response()->json([
                    'error' => 'La rubrique spécifiée dans la table ' . $outputClass . ' n\'existe pas.',
                    'rubrique_code' => $validatedData['output_rubrique_id'], // Inclure l'ID recherché dans la réponse
                    'suggestion' => 'Voulez-vous créer cette rubrique ?'
                ], 404);
            }

            // Créer un nouveau mappage dans la base de données
            $mapping = new Mapping();
            $mapping->input_rubrique = $validatedData['input_rubrique'];
            $mapping->name_rubrique = $validatedData['name_rubrique'];
            $mapping->output_rubrique_id = $validatedData['output_rubrique_id'];
            $mapping->output_type = $validatedData['output_type'];
            $mapping->company_folder_id = $validatedData['company_folder_id'];

            // Enregistrer le mappage dans la base de données
            if ($mapping->save()) {
                return response()->json(['success' => 'Mappage ajouté avec succès'], 201);
            } else {
                return response()->json(['error' => 'Erreur lors de l\'ajout du mappage'], 500);
            }
        }
    }

    public function updateMapping(Request $request, $id){
        
        $mapping = Mapping::findOrFail($request->id);

        $validatedData = $request->validate([
            'input_rubrique' => 'required|string|regex:/^\d{1,3}[A-Z]{0,2}$/',
            'name_rubrique' => 'required|string|max:255',
            'output_rubrique_id' => 'required|integer',
            'company_folder_id' => 'required|integer',
            'output_type' => 'required|string',
        ]);    

        if($mapping->update($validatedData)){
            return response()->json(['success' => 'Mappage modifié avec succès'], 200);
        } else {
            return response()->json(['error' => 'Erreur lors de la modification du mappage'], 500);
        }
    }
}
