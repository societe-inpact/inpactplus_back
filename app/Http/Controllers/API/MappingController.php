<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absences\Absence;
use App\Models\Absences\CustomAbsence;
use App\Models\Hours\CustomHour;
use App\Models\Hours\Hour;
use App\Models\Mapping\Mapping;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\InterfaceSoftware;
use App\Models\Misc\Software;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\Reader;
use function Laravel\Prompts\error;

class MappingController extends Controller
{
    protected $tableNames = [
        'Absence' => 'App\Models\Absences\Absence',
        'Absence personnalisée' => 'App\Models\Absences\CustomAbsence',
        'Heure' => 'App\Models\Hours\Hour',
        'Heure personnalisée' => 'App\Models\Hours\CustomHour',
        'Éléments variables' => 'App\Models\VariablesElements\VariableElement',
    ];

    protected $tableNamesRevers = [
        'App\Models\Absences\Absence' => 'Absence',
        'App\Models\Absences\CustomAbsence' => 'Absence personnalisée',
        'App\Models\Hours\Hour' => 'Heure',
        'App\Models\Hours\CustomHour' => 'Heure personnalisée',
        'App\Models\VariablesElements\VariableElement' => 'Éléments variables',
    ];

    // Fonction permettant de récupérer les mappings existants d'un dossier
    public function getMapping(Request $request)
    {
        $companyFolder = $request->get('company_folder_id');
        $companyFolderInfo = CompanyFolder::where('id',$companyFolder)->first();
        $interface = $companyFolderInfo->interface_id;

        // $interface = $request->get('interface_id');
        
        $softwaresNames = Software::all()->where('id',$interface)->first();

        if ($softwaresNames !== null){
            $idSoftware = $softwaresNames->interface_software_id;
        }else{
            return response()->json(['message' => 'L\'interface n\'existe pas','status' => 400]);
        }

        if ($idSoftware !== null){
            $columnindex = InterfaceSoftware::all()->where('id',$idSoftware)->first();
            $type_separateur = $columnindex->type_separateur;
            $format = $columnindex->format;
            $format = strtolower($format);
            $index_rubrique = $columnindex->colonne_rubrique-1;
            $colonne_matricule = $columnindex->colonne_matricule-1;
            
        }else{

            // interfaces spécifique
            $softwaresName = strtolower($softwaresNames["name"]);
            switch ($softwaresName){
                case "marathon":
                    $controller = new ConvertMEController();
                    $columnindex = $controller->formatFilesMarathon();
                    $type_separateur = $columnindex["separateur"];
                    $format = $columnindex ["format"];
                    $index_rubrique = $columnindex ["index_rubrique"];
                    $colonne_matricule = 0;
                    break; 

                default:
                    return response()->json(['success' => false, 'message' => 'il manque le paramétrage spécifique se l\'interface !','status' => 400]); 
                 
            }
        }

        if (!$companyFolder) {
            return response()->json("L'id du dossier est requis", 400);
        }

        if (!$request->hasFile($format)) {
            return response()->json('Aucun fichier importé');
        }

        // ajouter les condition de type de fichier

        $file = $request->file('csv');
        $reader = $this->prepareCsvReader($file->getPathname(),$type_separateur);
        $records = iterator_to_array($reader->getRecords(), true);
        $results = $this->processCsvRecords($records, $companyFolder,$index_rubrique,$colonne_matricule);

        return response()->json($results);
    }

    // Fonction permettant de configurer l'import du fichier
    protected function prepareCsvReader($path,$type_separateur)
    {
        $reader = Reader::createFromPath($path, 'r');
        $encoder = (new CharsetConverter())->inputEncoding('utf-8');
        $reader->addFormatter($encoder);
        $reader->setDelimiter($type_separateur);

        return $reader;
    }

    // Fonction permettant de récupérer les mappings existants d'un dossier
    protected function processCsvRecords($records, $companyFolder,$index_rubrique,$colonne_matricule)
    {
        $processedRecords = collect();
        $unmatchedRubriques = [];
        $results = [];
        
        $containsDigit = ctype_digit($records[0][$colonne_matricule]);
        if (($containsDigit) === false) {
            unset($records[0]);
        }

        foreach ($records as $record) {

            // colonne à ne pas prendre en compte
            if (!isset($record[$index_rubrique])) {
                continue;
            }
            // $inputRubrique = $this->findInputRubrique($record[$index_rubrique]);
            $inputRubrique = $record[$index_rubrique];

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
                            'type_rubrique' => $this->tableNamesRevers[$data['output_type']] ?? $data['output_type'],
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
        $validatedData = $this->validateMappingData($request);

        $companyFolder = $request->get('company_folder_id');
        if (!$companyFolder) {
            return response()->json("L'id du dossier est requis", 400);
        }

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
            // 'input_rubrique' => 'required|string|regex:/^[A-Za-z0-9]{1,3}$/',
            'input_rubrique' => 'required|string|max:255',
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
        $dataBis = [];

        // permet de modifier output_type en se basant sur le nom de la rubrique (si ce n'est pas null)

        $validatedData = $this->controleAbsenceHours($validatedData);

        if ($validatedData['name_rubrique'] !== null){
            $out = array("output_type"=>$this->tableNames[$validatedData['name_rubrique']]);
            $validatedData = array_replace($validatedData,$out);
        }

        // permet d'enregister les modifications
        foreach ($data as $entry) {     
            if ($entry['input_rubrique'] === $validatedData['input_rubrique']) {
                $entry['name_rubrique'] = $validatedData['name_rubrique'];
                $entry['output_rubrique_id'] = $validatedData['output_rubrique_id'];
                $entry['output_type'] = $validatedData['output_type'];
                $entry['is_used'] = $validatedData['is_used'];
                $dataBis[] = $entry; 
            }else{
                $dataBis[] = $entry;
            }
        }

        if ($data !== $dataBis) {
            $mapping->data = $dataBis;
            $mapping->save();
            return 'updated';
        }else{
            return 'nomodif';
        }
    }

    // Fonction de contrôle des absences perso et des heures perso

    private function controleAbsenceHours($validatedRequestData){

        if ($validatedRequestData['name_rubrique'] === "Absence"){
            
            $labelAbs = Absence::where("id",$validatedRequestData['output_rubrique_id'])->first();
            
            $absPerso = CustomAbsence::where("code",$labelAbs->code)->where("company_folder_id",$validatedRequestData['company_folder_id'])->first();
            if ($absPerso !== null){
                $out = array("name_rubrique"=>'Absence personnalisée', "output_rubrique_id"=>($absPerso->id));
                $validatedRequestData = array_replace($validatedRequestData,$out);
            }
        }

        if ($validatedRequestData['name_rubrique'] === "Heure personnalisée"){
            $labelHourCust = CustomHour::where("id",$validatedRequestData['output_rubrique_id'])->where("company_folder_id",$validatedRequestData['company_folder_id'])->first();
            $hour = Hour::where("code",$labelHourCust->code)->first();
            if ($hour !== null){
                $out = array("name_rubrique"=>'Heure', "output_rubrique_id"=>($hour->id));
                $validatedRequestData = array_replace($validatedRequestData,$out);
            }
        }
        return $validatedRequestData;
    }

    // Fonction permettant d'enregistrer un nouveau mapping en BDD
    public function storeMapping(Request $request)
    {
        $validatedRequestData = $this->validateMappingData($request);
        $companyFolder = $validatedRequestData['company_folder_id'];
        $mappedRubriques = Mapping::where('company_folder_id', $companyFolder)->get();

        $validatedRequestData = $this->controleAbsenceHours($validatedRequestData);

        if ($validatedRequestData['name_rubrique'] !== null){
            $out = array("output_type"=>$this->tableNames[$validatedRequestData['name_rubrique']]);
            $validatedRequestData = array_replace($validatedRequestData,$out);
        }

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
                            'error' => 'La rubrique d\'entrée ' . $validatedRequestData['input_rubrique'] . ' est déjà associée à la rubrique '
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
    protected function deleteMapping(Request $request)
    {

        // TODO : revoir le delete mapping 
        $request->validate([
            'user_id' => 'required|integer',
        ]);
        $userToDelete = Mapping::where('user_id', intval($request->user_id))->delete();
        if ($userToDelete){
            return response()->json(['message' => 'Mapping supprimé du dossier avec succès']);
        }
        return response()->json(['message' => 'Erreur lors de la suppression du mapping']);
    }

    protected function deleteAllMapping(){
        
    }

    // Fonction permettant de mettre à jour un mappings existant
    public function deleteOneLineMappingData($companyFolderId, $idDelete,$nameRubrique)
    {
        // permet de récupérer le mapping
        $mappingCompagny = Mapping::where("company_folder_id", $companyFolderId)->first();
        $data = $mappingCompagny->data;
        $dataBis = [];

        // permet d'enregister les modifications
        foreach ($data as $entry) {  
            if ((string)$entry['output_rubrique_id'] === (string)$idDelete && $entry['name_rubrique'] === $nameRubrique ) {
                // supprimer la valeur
            }else{
                $dataBis[] = $entry;
            }
        }

        if ($data !== $dataBis) {
            $mappingCompagny->data = $dataBis;
            $mappingCompagny->save();
            return 'updated';
        }else{
            return 'nomodif';
        }
    }  
}
