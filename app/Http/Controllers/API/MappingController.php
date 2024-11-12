<?php

namespace App\Http\Controllers\API;

use App\Classes\Rubric;
use App\Http\Controllers\Controller;
use App\Models\Absences\Absence;
use App\Models\Absences\CustomAbsence;
use App\Models\Hours\CustomHour;
use App\Models\Mapping\Mapping;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\InterfaceMapping;
use App\Models\Misc\InterfaceSoftware;
use App\Traits\HistoryResponseTrait;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Csv\CharsetConverter;
use League\Csv\Reader;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\JsonResponse;

class MappingController extends Controller
{
    use JSONResponseTrait;
    use HistoryResponseTrait;

    /**
     * Récupère les mappings existants d'un dossier.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getMapping(Request $request, $id)
    {
        // Vérifie si l'utilisateur a l'autorisation de lire les mappings
        $this->authorize('read_mapping', Mapping::class);

        // Récupère le fichier envoyé dans la requête
        $file = $request->file('file');
        // Récupère le dossier de l'entreprise avec ses interfaces
        $companyFolder = CompanyFolder::with('interfaces')->findOrFail($id);

        // Vérifie si le dossier existe
        if (!$companyFolder) {
            return $this->errorResponse('L\'id du dossier est requis');
        }

        // Vérifie si un fichier a été importé
        if (!$file) {
            return $this->errorResponse('Aucun fichier importé');
        }

        // Récupère l'interface associée à l'ID fourni
        $interface = InterfaceSoftware::findOrFail($request->interface_id);
        if ($interface) {
            // Récupère l'ID de mapping de l'interface
            $idInterfaceMapping = $interface->interface_mapping_id;

            // Si l'ID de mapping existe, récupère les informations de mapping
            if ($idInterfaceMapping !== null) {
                $columnIndex = InterfaceMapping::findOrFail($idInterfaceMapping);
                $separatorType = $columnIndex->separator_type;
                $extension = strtolower($columnIndex->extension);
                $indexRubric = $columnIndex->rubric - 1;
                $colonneMatricule = $columnIndex->employee_number - 1;

            } else {
                // Gestion des interfaces spécifiques
                $interfaceNames = strtolower($interface->name);
                switch ($interfaceNames) {
                    case "marathon":
                        // Traitement spécifique pour l'interface Marathon
                        $convertMEController = new ConvertMEController();
                        $columnIndex = $convertMEController->formatFilesMarathon();
                        $separatorType = $columnIndex["separator_type"];
                        $extension = $columnIndex["extension"];
                        $indexRubric = $columnIndex["index_rubric"];
                        $colonneMatricule = 0;
                        break;
                    case "rhis":
                        return $this->errorResponse('Algo de l\'interface à développer');
                    default:
                        return null;
                }
            }

            // Prépare le lecteur CSV avec le séparateur spécifié
            $reader = $this->prepareCsvReader($file->getPathname(), $separatorType);
            $records = iterator_to_array($reader->getRecords(), true);

            // Vérifie l'extension du fichier et traite en conséquence
            $fileExtension = strtolower($file->getClientOriginalExtension());
            if ($fileExtension === 'csv') {
                $reader = $this->prepareCsvReader($file->getPathname(), $separatorType);
                $records = iterator_to_array($reader->getRecords(), true);
            } elseif ($fileExtension === 'xlsx') {
                // Utilisation de PhpSpreadsheet pour les fichiers XLSX
                $spreadsheet = IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $records = $worksheet->toArray();
            } else {
                return $this->errorResponse('Format de fichier non supporté');
            }

            // Récupère l'ID du dossier de l'entreprise
            $companyFolderId = $companyFolder->id;
            // Traite les enregistrements du fichier et récupère les résultats
            $results = $this->processFileRecords($records, $companyFolderId, $indexRubric, $colonneMatricule);

            // Retourne la réponse de succès avec les résultats
            return $this->successResponse($results);
        } else {
            return $this->errorResponse('L\'interface n\'existe pas', 404);
        }
    }

    /**
     * Prépare le lecteur CSV avec le chemin et le type de séparateur spécifiés.
     * 
     * @param string $path
     * @param string $separatorType
     * @return Reader
     */
    protected function prepareCsvReader($path, $separatorType)
    {
        $reader = Reader::createFromPath($path, 'r');
        $encoder = (new CharsetConverter())->inputEncoding('utf-8');
        $reader->addFormatter($encoder);
        $reader->setDelimiter($separatorType);

        return $reader;
    }

    /**
     *  Fonction permettant de récupérer les mappings existants d'un dossier
     * 
     * @param array $records
     * @param int $companyFolderId
     * @param int $indexRubric
     * @param int $colonneMatricule
     * @return array
     */
    protected function processFileRecords($records, $companyFolderId, $indexRubric, $colonneMatricule)
    {
        $processedRecords = collect();
        $unmatchedRubrics = [];
        $results = [];

        // Vérifie si la première colonne contient un chiffre
        $containsDigit = ctype_digit($records[0][$colonneMatricule]);
        if (($containsDigit) === false) {
            unset($records[0]); // Supprime la première ligne si elle ne contient pas de chiffres
        }

        foreach ($records as $record) {
            // Vérifie si la colonne de la rubrique existe
            if (!isset($record[$indexRubric])) {
                continue; // Ignore les enregistrements sans rubrique
            }

            $inputRubric = $record[$indexRubric];

            // Traite les rubriques non déjà traitées
            if ($inputRubric && !$processedRecords->contains($inputRubric)) {
                $processedRecords->push($inputRubric);
                $mappingResult = $this->findMapping($inputRubric, $companyFolderId);
                if ($mappingResult) {
                    $results[] = $mappingResult; // Ajoute le résultat de mapping
                } else {
                    // Ajoute les rubriques non appariées
                    $unmatchedRubrics[] = [
                        'input_rubric' => $inputRubric,
                        'type_rubric' => null,
                        'output_rubric' => null,
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
        return array_merge($results, $unmatchedRubrics); // Retourne les résultats et les rubriques non appariées
    }

    // Fonction permettant de récupérer une rubric d'entrée
    protected function findInputRubric($rubric, $regex)
    {
        // Vérification si la rubric correspond au regex
        if (preg_match($regex, $rubric)) {
            return $rubric;
        }
        return null;
    }

    // Fonction permettant de récupérer les mappings existants d'un dossier
    protected function findMapping($inputRubric, $companyFolder)
    {
        $mappings = Mapping::with('folder')
            ->where('company_folder_id', $companyFolder)
            ->get();

        foreach ($mappings as $mapping) {
            foreach ($mapping->data as $data) {
                $data = new Rubric($data);
                if ($data->input_rubric === $inputRubric) {
                    if ($data->output_type) {
                        return [
                            'input_rubric' => $data->input_rubric,
                            'type_rubric' => $this->translateOutputModel($data->output_type),
                            'output_rubric' => $data->getSilaeRubric()->code,
                            'base_calcul' => $data->getSilaeRubric()->base_calcul,
                            'label' => $data->getSilaeRubric()->label,
                            'therapeutic_part_time' => $data->therapeutic_part_time,
                            'is_used' => $data->is_used,
                            'is_mapped' => true,
                            'company_folder_id' => $companyFolder,
                        ];
                    } else {
                        return [
                            'input_rubric' => $data->input_rubric,
                            'type_rubric' => $this->translateOutputModel($data->output_type),
                            'output_rubric' => '',
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

    // Fonction permettant de récupérer le Model d'une rubric
    public function resolveOutputModel($outputType, $outputRubricId = null)
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
            $this->setMappingHistory('Mapping', $user, $companyFolder->id, 'mapping', 'le ' . $date, 'Modification', $validatedData->input_rubric, $validatedData->output_type, $validatedData->is_used ? $validatedData->getSilaeRubric()->code : null, 'L\'utilisateur ' . $user->firstname . ' ' . $user->lastname . ' a mis à jour le mapping d\'un fichier',);
            return $this->successResponse('', 'Mapping mis à jour avec succès');
        } else {
            return $this->errorResponse('La rubric est introuvable', 404);
        }
    }


    // Fonction permettant de valider les données d'enregistrement d'un mapping
    protected function validateMappingData(Request $request)
    {
        $rubric = $request->validate([
            'input_rubric' => 'required|string|max:255',
            'type_rubric' => 'nullable|string|max:255',
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
            if ($entry['input_rubric'] === $rubricRequest->input_rubric) {
                $entry['type_rubric'] = $rubricRequest->type_rubric;
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
                        if ($data['type_rubric'] === 'Absence' && $data['output_rubric_id'] === $existingAbsence->id) {
                            $mappingData[$key] = [
                                "input_rubric" => $data['input_rubric'],
                                "is_used" => $data['is_used'],
                                "output_type" => $rubricRequest->output_type,
                                "type_rubric" => $rubricRequest->type_rubric,
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
        $mappedRubrics = Mapping::where('company_folder_id', $companyFolderId)->get();
        $validatedRequestData = $this->checkExistingAbsence($validatedRequestData, $companyFolderId);
        
        foreach ($mappedRubrics as $mappedRubric) {
            $allMappedRubrics = $mappedRubric->data;
            foreach ($allMappedRubrics as $inputMappedRubric) {
                $isUsed = filter_var($validatedRequestData->is_used, FILTER_VALIDATE_BOOLEAN) || filter_var($inputMappedRubric['is_used'], FILTER_VALIDATE_BOOLEAN);
                if ($inputMappedRubric['input_rubric'] === $validatedRequestData->input_rubric) {
                    if ($isUsed === false) {
                        return $this->errorResponse('La rubric d\'entrée ' . $validatedRequestData->input_rubric . ' n\'est pas utilisée', 409);
                    } else {
                        $silaeCode = $validatedRequestData->getSilaeRubric() ? $validatedRequestData->getSilaeRubric()->code : 'non défini';
                        return $this->errorResponse('La rubric d\'entrée ' . $validatedRequestData->input_rubric . ' est déjà associée à la rubric ' . $silaeCode, 409);
                    }
                }
            }
        }

        $savedMapping = $this->saveMappingData($companyFolderId, $validatedRequestData, $request);
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
            'type_rubric' => $validatedData->type_rubric,
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
            return $mapping;
        } else {
            return Mapping::create([
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
            'type_rubric' => 'required|string',
            'input_rubric' => '',
            'output_type' => ''
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $companyFolderId = $request['company_folder_id'];
        $outputRubricId = $request['output_rubric_id'];
        $nameRubric = $request['type_rubric'];
        $inputRubric = $request['input_rubric'];
        $outputType = $request['output_type'];

        // Récupérer le mapping du dossier
        $mappingCompagny = Mapping::where("company_folder_id", $companyFolderId)->first();
        $data = $mappingCompagny->data;
        $dataBis = [];

        // Enregistrer les modifications
        foreach ($data as $entry) {
            if ((string)$entry['output_rubric_id'] === (string)$outputRubricId && $entry['type_rubric'] === $nameRubric) {
                if ($inputRubric) {
                    if ((string)$entry['input_rubric'] === (string)$inputRubric) {
                        // Supprimer la valeur
                        continue;
                    } else {
                        $dataBis[] = $entry;
                    }
                } else {
                    // Supprimer la valeur
                    continue;
                }
            } else {
                $dataBis[] = $entry;
            }
        }

        // Vérifie si des modifications ont été apportées
        if ($data !== $dataBis) {
            $mappingCompagny->data = $dataBis;
            $mappingCompagny->save();

            // Récupérer l'objet correspondant à output_rubric_id
            $outputRubric = $this->resolveOutputModel($outputType, $outputRubricId);

            // Vérifiez si l'objet a été trouvé avant d'appeler getSilaeRubric()
            if ($outputRubric) {
                $this->setMappingHistory(
                    'Mapping',
                    $user,
                    $companyFolderId,
                    'mapping',
                    now()->format('d/m/Y à H:i'),
                    'Suppression',
                    $inputRubric,
                    $nameRubric,
                    $outputRubricId ? $outputRubricId->getSilaeRubric()->code : null,
                    "L'utilisateur {$user->firstname} {$user->lastname} a supprimé une ligne de mapping d'un fichier"
                );
            }

            return $this->successResponse('', 'Ligne de mapping supprimée du dossier avec succès');
        } else {
            return $this->successResponse('', 'Ligne de mapping non modifiée');
        }
    }
}
