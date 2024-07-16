<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mapping\Mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use League\Csv\CharsetConverter;
use League\Csv\Reader;

class MappingController extends Controller
{
    protected $tableNames = [
        'App\Models\Absences\Absence' => 'Absence',
        'App\Models\Absences\CustomAbsence' => 'Absence personnalisée',
        'App\Models\Hours\Hour' => 'Heure',
        'App\Models\Hours\CustomHour' => 'Heure personnalisée',
        'App\Models\VariableElement\VariableElement' => 'Éléments variables',
    ];

    // Fonction permettant de récupérer les mappings existants d'un dossier
    public function getMapping(Request $request)
    {
        $companyFolder = $request->get('company_folder_id');
        if (!$companyFolder) {
            return response()->json("L'id du dossier est requis", 400);
        }

        if (!$request->hasFile('csv')) {
            return response()->json('Aucun fichier importé');
        }

        $file = $request->file('csv');
        $reader = $this->prepareCsvReader($file->getPathname());
        $records = $reader->getRecords();
        $results = $this->processCsvRecords($records, $companyFolder);

        return response()->json($results);
    }

    // Fonction permettant de configurer l'import du fichier
    protected function prepareCsvReader($path)
    {
        $reader = Reader::createFromPath($path, 'r');
        $encoder = (new CharsetConverter())->inputEncoding('utf-8');
        $reader->addFormatter($encoder);
        $reader->setDelimiter(';');

        return $reader;
    }

    // Fonction permettant de récupérer les mappings existants d'un dossier
    protected function processCsvRecords($records, $companyFolder)
    {
        $processedRecords = collect();
        $unmatchedRubriques = [];
        $rubriqueRegex = '/^[A-Za-z0-9]{1,3}$/';
        $results = [];

        foreach ($records as $record) {
            // $record[3] représente la colonne RUBRIQUE
            if (!isset($record[3])) {
                continue;
            }
            $inputRubrique = $this->findInputRubrique($record[3], $rubriqueRegex);

            if ($inputRubrique && !$processedRecords->contains($inputRubrique)) {
                $processedRecords->push($inputRubrique);
                $mappingResult = $this->findMapping($inputRubrique, $companyFolder);
                if ($mappingResult) {
                    $results[] = $mappingResult;
                } else {
                    $unmatchedRubriques[] = [
                        'input_rubrique' => $inputRubrique,
                        'type_rubrique' => null,
                        'output_rubrique' => null,
                        'base_calcul' => null,
                        'label' => null,
                        'is_mapped' => false,
                        'is_used' => false,
                        'company_folder_id' => $companyFolder,
                    ];
                }
            }
        }
        return array_merge($results, $unmatchedRubriques);
    }

    // Fonction permettant de récupérer une rubrique d'entrée
    protected function findInputRubrique($rubrique, $regex)
    {
        // Vérification si la rubrique correspond au regex
        if (preg_match($regex, $rubrique)) {
            return $rubrique;
        }
        return null;
    }

    // Fonction permettant de récupérer les mappings existants d'un dossier
    protected function findMapping($inputRubrique, $companyFolder)
    {
        $mappings = Mapping::with('folder')
            ->where('company_folder_id', $companyFolder)
            ->get();

        foreach ($mappings as $mapping) {
            foreach ($mapping->data as $data) {
                if ($data['input_rubrique'] === $inputRubrique) {
                    $output = $this->resolveOutputModel($data['output_type'], $data['output_rubrique_id']);
                    if ($output) {
                        return [
                            'input_rubrique' => $data['input_rubrique'],
                            'type_rubrique' => $this->tableNames[$data['output_type']] ?? $data['output_type'],
                            'output_rubrique' => $output->code,
                            'base_calcul' => $output->base_calcul,
                            'label' => $output->label,
                            'is_used' => $data['is_used'],
                            'is_mapped' => true,
                            'company_folder_id' => $companyFolder,
                        ];
                    }else{
                        return [
                            'input_rubrique' => $data['input_rubrique'],
                            'type_rubrique' => $data['output_type'],
                            'output_rubrique' => '',
                            'base_calcul' => '',
                            'label' => '',
                            'is_used' => $data['is_used'],
                            'is_mapped' => true,
                            'company_folder_id' => $companyFolder,
                        ];
                    }
                }
            }
        }

        return null;
    }

    // Fonction permettant de récupérer le Model d'une rubrique
    protected function resolveOutputModel($outputType, $outputRubriqueId)
    {
        if (!class_exists($outputType)) {
            return null;
        }

        $outputModelClass = App::make($outputType);
        return $outputModelClass->find($outputRubriqueId);
    }

    // Fonction permettant de mettre à jour un mapping existant
    public function updateMapping(Request $request, $id)
    {
        $companyFolder = $request->get('company_folder_id');

        if (!$companyFolder) {
            return response()->json("L'id du dossier est requis", 400);
        }

        $validatedData = $this->validateMappingData($request);
        $mapping = Mapping::with('folder')
            ->where('company_folder_id', $companyFolder)
            ->findOrFail($id);

        if ($mapping->company_folder_id !== intval($validatedData['company_folder_id'])) {
            return response()->json(['error' => 'Le dossier de l\'entreprise ne correspond pas.'], 403);
        }

        $updateResult = $this->updateMappingData($mapping, $validatedData);
        if ($updateResult === 'updated') {
            return response()->json(['message' => 'Mapping mis à jour avec succès']);
        } else {
            return response()->json(['error' => 'Rubrique introuvable'], 404);
        }
    }



    // Fonction permettant de valider les données d'enregistrement d'un mapping
    protected function validateMappingData(Request $request)
    {
        return $request->validate([
            'input_rubrique' => 'required|string|regex:/^[A-Za-z0-9]{1,3}$/',
            'name_rubrique' => 'nullable|string|max:255',
            'output_rubrique_id' => 'nullable|integer',
            'company_folder_id' => 'required',
            'output_type' => 'nullable|string',
            'is_used' => 'required|boolean',
        ]);
    }

    // Fonction permettant de mettre à jour un mappings existant
    protected function updateMappingData($mapping, $validatedData)
    {
        $data = $mapping->data;

        foreach ($data as &$entry) {
            if ($entry['input_rubrique'] === $validatedData['input_rubrique']) {
                $entry['name_rubrique'] = $validatedData['name_rubrique'];
                $entry['output_rubrique_id'] = $validatedData['output_rubrique_id'];
                $entry['output_type'] = $validatedData['output_type'];
                $entry['is_used'] = $validatedData['is_used'];
                $mapping->data = $data;
                $mapping->save();
                return 'updated';
            }
        }
    }



    // Fonction permettant d'enregistrer un nouveau mapping en BDD
    public function storeMapping(Request $request)
    {
        $validatedRequestData = $this->validateMappingData($request);
        $companyFolder = $validatedRequestData['company_folder_id'];
        $mappedRubriques = Mapping::where('company_folder_id', $companyFolder)->get();
        foreach ($mappedRubriques as $mappedRubrique) {
            $allMappedRubriques = $mappedRubrique->data;
            foreach ($allMappedRubriques as $inputMappedRubrique) {
                $isUsed = filter_var($validatedRequestData['is_used'], FILTER_VALIDATE_BOOLEAN) || filter_var($inputMappedRubrique['is_used'], FILTER_VALIDATE_BOOLEAN);
                if ($inputMappedRubrique['input_rubrique'] === $validatedRequestData['input_rubrique']) {

                    if ($isUsed === false){
                        return response()->json([
                            'error' => 'La rubrique d\'entrée ' . $validatedRequestData['input_rubrique'] . ' n\'est pas utilisée',
                        ], 409);
                    }else{
                        return response()->json([
                            'error' => 'La rubrique d\'entrée ' . $validatedRequestData['input_rubrique'] . ' est déjà associée à la rubrique ' . $this->getSilaeRubrique($validatedRequestData)->code,
                        ], 409);
                    }
                }
//                if (!$this->validateOutputClassAndRubrique($validatedRequestData) && $isUsed) {
//                    return response()->json([
//                        'error' => 'La rubrique ou le type de rubrique spécifié n\'existe pas.',
//                    ], 404);
//                }
            }
        }
        $this->saveMappingData($companyFolder, $validatedRequestData);
        return response()->json(['success' => 'Mapping ajouté avec succès'], 201);
    }

    // Fonction permettant de transformer la rubrique d'entrée mappée en rubrique de sortie SILAE
    private function getSilaeRubrique($rubrique)
    {
        $typeRubrique = $rubrique['output_type'];
        $outputRubrique = $rubrique['output_rubrique_id'];
        if (class_exists($typeRubrique)) {
            return $typeRubrique::find($outputRubrique);
        }
        return false;
    }

    // Fonction permettant de récupérer la rubrique via son Model
    protected function validateOutputClassAndRubrique($validatedData)
    {
        $outputClass = $validatedData['output_type'];

        if (!class_exists($outputClass)) {
            return false;
        }

        $outputRubrique = $outputClass::find($validatedData['output_rubrique_id']);

        return $outputRubrique !== null;
    }

    // Fonction permettant d'enregistrer un nouveau mapping en BDD
    protected function saveMappingData($companyFolder, $validatedData)
    {
        $newMapping = [
            'input_rubrique' => $validatedData['input_rubrique'],
            'name_rubrique' => $validatedData['name_rubrique'],
            'output_rubrique_id' => $validatedData['output_rubrique_id'],
            'output_type' => $validatedData['output_type'],
            'is_used' => $validatedData['is_used']
        ];

        $mapping = Mapping::where('company_folder_id', $companyFolder)->first();

        if ($mapping) {
            $existingData = $mapping->data;
            $existingData[] = $newMapping;
            $mapping->data = $existingData;
            $mapping->save();
        } else {
            Mapping::create([
                'company_folder_id' => $companyFolder,
                'data' => [$newMapping],
            ]);
        }
    }

    // Fonction permettant de supprimer un mapping existant
    protected function deleteMapping()
    {
        // TODO
    }
}
