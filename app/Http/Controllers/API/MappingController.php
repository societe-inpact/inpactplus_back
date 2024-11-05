<?php

namespace App\Http\Controllers\API;

use App\Classes\Rubric;
use App\Http\Controllers\Controller;
use App\Models\Absences\Absence;
use App\Models\Absences\CustomAbsence;
use App\Models\Hours\CustomHour;
use App\Models\Hours\Hour;
use App\Models\Mapping\Mapping;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\InterfaceMapping;
use App\Models\Misc\InterfaceSoftware;
use App\Traits\HistoryResponseTrait;
use App\Traits\JSONResponseTrait;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use League\Csv\CharsetConverter;
use League\Csv\Reader;
use Illuminate\Support\Facades\Validator;

class MappingController extends Controller
{
    use JSONResponseTrait;
    use HistoryResponseTrait;


    // Fonction permettant de récupérer les mappings existants d'un dossier
    public function getMapping(Request $request, $id)
    {
        $this->authorize('read_mapping', Mapping::class);

        $file = $request->file('file');
        $companyFolder = CompanyFolder::with('interfaces')->findOrFail($id);

        if (!$companyFolder) {
            return $this->errorResponse('L\'id du dossier est requis');
        }

        if (!$file) {
            return $this->errorResponse('Aucun fichier importé');
        }

        $interface = InterfaceSoftware::findOrFail($request->interface_id);
        if ($interface) {
            $idInterfaceMapping = $interface->interface_mapping_id;

            if ($idInterfaceMapping !== null) {
                $columnIndex = InterfaceMapping::findOrFail($idInterfaceMapping);
                $separatorType = $columnIndex->separator_type;
                $extension = strtolower($columnIndex->extension);
                $indexRubrique = $columnIndex->rubric - 1;
                $colonneMatricule = $columnIndex->employee_number - 1;

            } else {
                // interfaces spécifique
                $interfaceNames = strtolower($interface->name);
                switch ($interfaceNames) {
                    case "marathon":
                        $convertMEController = new ConvertMEController();
                        $columnIndex = $convertMEController->formatFilesMarathon();
                        $separatorType = $columnIndex["separator_type"];
                        $extension = $columnIndex["extension"];
                        $indexRubrique = $columnIndex["index_rubrique"];
                        $colonneMatricule = 0;
                        break;
                    case "rhis":
                        return $this->errorResponse('Algo de l\'interface à développer');
                    default:
                        return null;
                }
            }

            $reader = $this->prepareCsvReader($file->getPathname(), $separatorType);
            $records = iterator_to_array($reader->getRecords(), true);

            $fileExtension = strtolower($file->getClientOriginalExtension());
            if ($fileExtension === 'csv') {
                $reader = $this->prepareCsvReader($file->getPathname(), $separatorType);
                $records = iterator_to_array($reader->getRecords(), true);
            } elseif ($fileExtension === 'xlsx') {
                // Utilisation de PhpSpreadsheet pour les fichiers XLSX
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $records = $worksheet->toArray();
            } else {
                return $this->errorResponse('Format de fichier non supporté');
            }

            $companyFolderId = $companyFolder->id;
            $results = $this->processFileRecords($records, $companyFolderId, $indexRubrique, $colonneMatricule);

            return $this->successResponse($results);
        } else {
            return $this->errorResponse('L\'interface n\'existe pas', 404);
        }
    }


    // Fonction permettant de configurer l'import du fichier
    protected function prepareCsvReader($path, $separatorType)
    {
        $reader = Reader::createFromPath($path, 'r');
        $encoder = (new CharsetConverter())->inputEncoding('utf-8');
        $reader->addFormatter($encoder);
        $reader->setDelimiter($separatorType);

        return $reader;
    }


    // Fonction permettant de récupérer les mappings existants d'un dossier
    protected function processFileRecords($records, $companyFolderId, $indexRubrique, $colonneMatricule)
    {
        $processedRecords = collect();
        $unmatchedRubriques = [];
        $results = [];

        $containsDigit = ctype_digit($records[0][$colonneMatricule]);
        if (($containsDigit) === false) {
            unset($records[0]);
        }

        foreach ($records as $record) {

            // colonne à ne pas prendre en compte
            if (!isset($record[$indexRubrique])) {
                continue;
            }

            $inputRubrique = $record[$indexRubrique];

            if ($inputRubrique && !$processedRecords->contains($inputRubrique)) {
                $processedRecords->push($inputRubrique);
                $mappingResult = $this->findMapping($inputRubrique, $companyFolderId);
                if ($mappingResult) {
                    $results[] = $mappingResult;
                } else {
                    $unmatchedRubriques[] = [
                        'input_rubrique' => $inputRubrique,
                        'type_rubrique' => null,
                        'output_rubrique' => null,
                        'base_calcul' => null,
                        'label' => null,
                        'therapeutic_part_time' => null,
                        'is_mapped' => false,
                        'is_used' => false,
                        'company_folder_id' => $companyFolderId,
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
                $data = new Rubric($data);
                if ($data->input_rubrique === $inputRubrique) {
                    if ($data->output_type) {
                        return [
                            'input_rubrique' => $data->input_rubrique,
                            'type_rubrique' => $this->translateOutputModel($data->output_type),
                            'output_rubrique' => $data->getSilaeRubric()->code,
                            'base_calcul' => $data->getSilaeRubric()->base_calcul,
                            'label' => $data->getSilaeRubric()->label,
                            'therapeutic_part_time' => $data->therapeutic_part_time,
                            'is_used' => $data->is_used,
                            'is_mapped' => true,
                            'company_folder_id' => $companyFolder,
                        ];
                    } else {
                        return [
                            'input_rubrique' => $data->input_rubrique,
                            'type_rubrique' => $this->translateOutputModel($data->output_type),
                            'output_rubrique' => '',
                            'base_calcul' => '',
                            'label' => '',
                            'is_used' => $data->is_used,
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
    public function resolveOutputModel($outputType, $outputRubriqueId = null)
    {
        $modelTranslate = [
            'Absence' => 'Absences',
            'CustomAbsence' => 'Absences',
            'Hour' => 'Hours',
            'CustomHour' => 'Hours',
            'VariableElement' => 'VariablesElements',
        ];

        // Déterminer le sous-dossier du modèle
        $model = $modelTranslate[$outputType] ?? null;

        // Construire le chemin complet du modèle
        $namespacePrefix = 'App\Models\\';
        $fullOutputType = $model ? $namespacePrefix . $model . '\\' . $outputType : $namespacePrefix . $outputType;

        // Vérifier si la classe existe
        if (!class_exists($fullOutputType)) {
            return null;
        }

        return $fullOutputType;
    }

    public function translateOutputModel($outputType)
    {
        if (isset($outputType)) {
            preg_match('/[^\\\]+$/', $outputType, $matches);
            $outputType = $matches[0];

            $modelTranslation = [
                'Absence' => 'Absence',
                'CustomAbsence' => 'Absence personnalisée',
                'Hour' => 'Heure',
                'CustomHour' => 'Heure personnalisée',
                'VariableElement' => 'Élément variable'
            ];

            return $modelTranslation[$outputType];
        }
        return null;
    }

    // Fonction permettant de mettre à jour un mapping existant
    public function updateMapping(Request $request, $id)
    {
        $user = Auth::user();
        $companyFolder = CompanyFolder::with('mappings')->findOrFail($request->company_folder_id);

        if (!$companyFolder) {
            return $this->errorResponse('L\'id du dossier est requis', 400);
        }
        $validatedData = $this->validateMappingData($request);
        $mapping = Mapping::with('folder')
            ->where('company_folder_id', $companyFolder->id)
            ->findOrFail($id);


        if ($mapping->company_folder_id !== intval($validatedData->company_folder_id)) {
            return $this->errorResponse('Le dossier de l\'entreprise ne correspond pas', 403);
        }

        $updateResult = $this->updateMappingData($mapping, $validatedData);

        if ($updateResult === 'updated') {
            $date = now()->format('d/m/Y à H:i');
            $this->setMappingHistory('Mapping', $user, $companyFolder->id, 'mapping', 'le ' . $date, 'Modification', $validatedData->input_rubrique, $validatedData->output_type, $validatedData->is_used ? $validatedData->getSilaeRubric()->code : null, 'L\'utilisateur ' . $user->firstname . ' ' . $user->lastname . ' a mis à jour le mapping d\'un fichier',);
            return $this->successResponse('', 'Mapping mis à jour avec succès');
        } else {
            return $this->errorResponse('La rubrique est introuvable', 404);
        }
    }


    // Fonction permettant de valider les données d'enregistrement d'un mapping
    protected function validateMappingData(Request $request)
    {
        $rubric = $request->validate([
            'input_rubric' => 'required|string|max:255',
            'name_rubric' => 'nullable|string|max:255',
            'output_rubric_id' => 'nullable|integer',
            'therapeutic_part_time' => 'nullable|numeric',
            'company_folder_id' => 'required',
            'output_type' => 'nullable|string',
            'is_used' => 'required|boolean',
        ]);

        $rubric = new Rubric($rubric);
        $rubric->output_type = $this->resolveOutputModel($rubric->output_type, $rubric->output_rubric_id);
        return $rubric;
    }

    // Fonction permettant de mettre à jour un mappings existant
    protected function updateMappingData($mapping, $rubricRequest)
    {
        $data = $mapping->data;
        foreach ($data as &$entry) {
            if ($entry['input_rubrique'] === $rubricRequest->input_rubrique) {
                $entry['name_rubrique'] = $rubricRequest->name_rubrique;
                $entry['output_rubric_id'] = $rubricRequest->output_rubric_id;
                $entry['output_type'] = $rubricRequest->output_type;
                $entry['therapeutic_part_time'] = $rubricRequest->therapeutic_part_time;
                $entry['is_used'] = $rubricRequest->is_used;
                $mapping->data = $data;
                $mapping->save();
                return 'updated';
            }
        }
    }

    // Fonction de contrôle des absences perso et des heures perso

    private function checkExistingAbsence($rubricRequest, $companyFolderId)
    {
        $mapping = Mapping::where('company_folder_id', $companyFolderId)->first();
        $mappingData = $mapping->data;
        if (str_contains($rubricRequest->output_type, 'CustomAbsence')) {
            $existingCustomAbsence = CustomAbsence::find($rubricRequest->output_rubric_id);
            if ($existingCustomAbsence) {
                $existingAbsence = Absence::where('code', $existingCustomAbsence->code)->first();
                if ($existingAbsence) {
                    foreach ($mappingData as $key => $data) {
                        if ($data['name_rubrique'] === 'Absence' && $data['output_rubric_id'] === $existingAbsence->id) {
                            $mappingData[$key] = [
                                "input_rubrique" => $data['input_rubrique'],
                                "is_used" => $data['is_used'],
                                "output_type" => $rubricRequest->output_type,
                                "name_rubrique" => $rubricRequest->name_rubrique,
                                "therapeutic_part_time" => $rubricRequest->therapeutic_part_time,
                                "output_rubric_id" => $existingCustomAbsence->id
                            ];
                            // Mise à jour des données du mapping
                            $mapping->data = $mappingData;
                            $mapping->update();
                            break;
                        }
                    }
                }
            }
        }
        return $rubricRequest;
    }


    // Fonction permettant d'enregistrer un nouveau mapping en BDD
    public function storeMapping(Request $request)
    {
        $this->authorize('create_mapping', Mapping::class);
        
        $user = Auth::user();
        $validatedRequestData = $this->validateMappingData($request);
        $companyFolderId = $validatedRequestData->company_folder_id;
        $mappedRubriques = Mapping::where('company_folder_id', $companyFolderId)->get();
        $validatedRequestData = $this->checkExistingAbsence($validatedRequestData, $companyFolderId);
        
        foreach ($mappedRubriques as $mappedRubrique) {
            $allMappedRubriques = $mappedRubrique->data;
            foreach ($allMappedRubriques as $inputMappedRubrique) {
                $isUsed = filter_var($validatedRequestData->is_used, FILTER_VALIDATE_BOOLEAN) || filter_var($inputMappedRubrique['is_used'], FILTER_VALIDATE_BOOLEAN);
                if ($inputMappedRubrique['input_rubric'] === $validatedRequestData->input_rubric) {
                    if ($isUsed === false) {
                        return $this->errorResponse('La rubrique d\'entrée ' . $validatedRequestData->input_rubric . ' n\'est pas utilisée', 409);
                    } else {
                        $silaeCode = $validatedRequestData->getSilaeRubric() ? $validatedRequestData->getSilaeRubric()->code : 'non défini';
                        return $this->errorResponse('La rubrique d\'entrée ' . $validatedRequestData->input_rubric . ' est déjà associée à la rubrique ' . $silaeCode, 409);
                    }
                }
            }
        }

        $savedMapping = $this->saveMappingData($companyFolderId, $validatedRequestData);
        $date = now()->format('d/m/Y à H:i');
        
        $silaeCode = null;
        if ($validatedRequestData->is_used && method_exists($validatedRequestData, 'getSilaeRubric')) {
            $silaeRubric = $validatedRequestData->getSilaeRubric();
            $silaeCode = $silaeRubric ? $silaeRubric->code : null;
        }
        
        $this->setMappingHistory(
            'Mapping', 
            $user, 
            $companyFolderId, 
            'mapping', 
            'le ' . $date, 
            'Création', 
            $validatedRequestData->input_rubric, 
            $this->translateOutputModel($validatedRequestData->output_type), 
            $silaeCode,
            'L\'utilisateur ' . $user->firstname . ' ' . $user->lastname . ' a créé un mapping d\'un fichier'
        );

        // Retourner une réponse plus détaillée
        return $this->successResponse(
            [
                'mapping' => $savedMapping,
                'company_folder_id' => $companyFolderId,
                'created_at' => $date
            ],
            'Le mapping a été créé avec succès',
            201
        );
    }

    // Fonction permettant d'enregistrer un nouveau mapping en BDD
    protected function saveMappingData($companyFolder, $validatedData, ?Request $request = null)
    {
        $newMapping = [
            'input_rubric' => $validatedData->input_rubric,
            'name_rubric' => $validatedData->name_rubric,
            'output_rubric_id' => $validatedData->output_rubric_id,
            'therapeutic_part_time' => $validatedData->therapeutic_part_time,
            'output_type' => $validatedData->output_type,
            'is_used' => $validatedData->is_used,
        ];

        $mapping = Mapping::where('company_folder_id', $companyFolder)->first();
        if ($validatedData->is_used) {
            switch ($this->translateOutputModel($validatedData->output_type)) {
                case 'CustomHour' :
                {
                    $existingCustomHour = CustomHour::all()->where('id', '=', $validatedData->output_rubric_id);
                    if ($existingCustomHour->isEmpty()) {
                        if ($request) {
                            $createNewCustomHourRequest = new Request([
                                'code' => $request->code,
                                'label' => $request->label,
                                'company_folder_id' => $companyFolder,
                            ]);
                            $hourController = new HourController();
                            $hourController->createCustomHour($createNewCustomHourRequest);
                        }
                    }
                }
                case 'CustomAbsence' :
                {
                    $existingCustomAbsence = CustomAbsence::all()->where('id', '=', $validatedData->output_rubric_id);
                    if ($existingCustomAbsence->isEmpty()) {
                        if ($request) {
                            $createNewCustomAbsenceRequest = new Request([
                                'code' => $request->code,
                                'label' => $request->label,
                                'base_calcul' => $request->base_calcul,
                                'therapeutic_part_time' => $request->therapeutic_part_time ?? null,
                                'company_folder_id' => $companyFolder,
                            ]);
                            $absenceController = new CustomAbsenceController();
                            $absenceController->createCustomAbsence($createNewCustomAbsenceRequest);
                        }
                    }
                }
                default:
            }
        }
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
    protected function deleteMapping($id)
    {
        $mappingCompagny = Mapping::where("id", $id)->first();
        $dataBis = [];
        $mappingCompagny->data = $dataBis;
        if ($mappingCompagny->save()) {
            return $this->successResponse('', 'Mapping supprimé du dossier avec succès');
        }
        return $this->errorResponse('Erreur lors de la suppression du mapping', 500);
    }

    public function deleteOneLineMappingData(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'company_folder_id' => 'required|integer',
            'output_rubric_id' => 'required|integer',
            'name_rubric' => 'required|string',
            'input_rubric' => '',
            'output_type' => ''
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }
        $companyFolderId = $request['company_folder_id'];
        $outputRubricId = $request['output_rubric_id'];
        $nameRubric = $request['name_rubric'];
        $inputRubric = $request['input_rubric'];
        $outputType = $request['output_type'];
        $isUsed = $request['is_used'];

        // permet de récupérer le mapping
        $mappingCompagny = Mapping::where("company_folder_id", $companyFolderId)->first();
        $data = $mappingCompagny->data;
        $dataBis = [];

        // permet d'enregister les modifications
        foreach ($data as $entry) {
            // si c'est une valeur ne pas utiliser, il faut modifier le 'name_rubric'
            if (!isset($entry['name_rubric'])) {
                $entry['is_used'] = false;
                $entry['output_rubric_id'] = 0;
            }
            if ((string)$entry['output_rubric_id'] === (string)$outputRubricId && $entry['name_rubric'] === $nameRubric) {
                if ($inputRubric) {
                    if ((string)$entry['input_rubric'] === (string)$inputRubric) {
                        // supprimer la valeur
                    } else {
                        $dataBis[] = $entry;
                    }
                } else {
                    // supprimer la valeur
                }
            } else {
                $dataBis[] = $entry;
            }
        }

        if ($data !== $dataBis) {
            $mappingCompagny->data = $dataBis;
            $mappingCompagny->save();
            $date = now()->format('d/m/Y à H:i');
            $this->setMappingHistory('Mapping', $user, $companyFolderId, 'mapping', 'le ' . $date, 'Suppression', $inputRubric, $nameRubric, $outputRubricId->getSilaeRubric()->code, 'L\'utilisateur ' . $user->firstname . ' ' . $user->lastname . ' a supprimé une ligne de mapping d\'un fichier');
            return $this->successResponse('', 'Ligne de mapping supprimée du dossier avec succès');
        } else {
            return $this->successResponse('', 'Ligne de mapping non modifiée');
        }
    }
}
