<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employees\UserCompanyFolder;
use App\Models\Mapping\Mapping;
use App\Traits\JSONResponseTrait;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Controllers\RuntimeException;
use App\Http\Controllers\API\ConvertInterfaceController;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\InterfaceSoftware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Writer;
use function Symfony\Component\String\s;

class ConvertController extends BaseController
{
    use JSONResponseTrait;

    public function indexColumn($nominterface)
    {
        $controller = new InterfaceMappingController();
        return $controller->getInterfaceMapping($nominterface);
    }


    public function importFile(Request $request): JsonResponse
    {
        // Récupération et validation de l'interface
        $idInterface = $request->get('interface_id');
        $interfaceSoftware = InterfaceSoftware::find($idInterface);

        if ($interfaceSoftware === null) {
            return $this->errorResponse('L\'interface n\'existe pas', 404);
        }

        $idSoftware = $interfaceSoftware->interface_mapping_id;

        if ($idSoftware === null) {
            $softwaresName = strtolower($interfaceSoftware->name);
            switch ($softwaresName){
                case "marathon":
                    $controller = new ConvertMEController();
                    $columnindex = $controller->formatFilesMarathon();
                    $type_separateur = $columnindex["separator_type"];
                    $format = $columnindex ["extension"];
                    break;

                default:
                    return response()->json(['success' => false, 'message' => 'il manque le paramétrage spécifique se l\'interface !','status' => 400]);

            }
        } else {
            $idsoftware = InterfaceSoftware::findOrFail($idInterface);
            $columnindex = $this->indexColumn($idsoftware->interface_mapping_id);
            $type_separateur = $columnindex->type_separateur;
            $format = $columnindex->format;
        }

        $type_separateur = $columnindex['separator_type'];
        $extension = strtolower($columnindex['extension']);

        // Validation du fichier en fonction de l'extension
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
        ]);

        if (!$request->hasFile('csv')) {
            return $this->errorResponse('Veuillez importer un fichier', 400);
        }

        $file = $request->file('csv');


        return match ($extension) {
            'csv' => $this->handleCsvFile($file, $type_separateur),
            'xls' => $this->errorResponse('Le support XLS n\'est pas encore implémenté'),
            default => $this->errorResponse('Le format de fichier' . $extension . 'n\'est pas supporté')
        };
    }

    private function handleCsvFile($file, $delimiter): JsonResponse
    {
        $encoder = (new CharsetConverter())->inputEncoding('iso-8859-15')->outputEncoding('UTF-8');;
        $extension = fn(array $row): array => array_map('strtoupper', $row);

        $reader = Reader::createFromPath($file->getPathname(), 'r');
        $reader->addFormatter($encoder);
        $reader->setDelimiter($delimiter);
        $reader->addFormatter($extension);
        $reader->setHeaderOffset(null);

        $records = iterator_to_array($reader->getRecords(), true);

        if ($records) {
            return $this->successResponse($records, 'Votre fichier a été importé');
        }

        return $this->errorResponse('Aucun enregistrement trouvé dans le fichier', 400);
    }


    /**
     * Écrit les collections dans un fichier CSV.
     *
     * @param array $data Collections converties
     *
     * @throws Exception
     * @throws RuntimeException
     */
    private function writeToFile(array $data, $date)
    {
        // TODO : Récupérer l'instance du dossier et de l'utilisateur + path convert

        $filename = 'EVY_' . $date; // TODO : Modifier le nom du fichier
        $directory = storage_path('csv');
        $csvPath = $directory . '/' . $filename . '.csv';

        // Création du dossier s'il n'existe pas
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $csv = Writer::createFromPath($csvPath, 'w+');
        $csv->setDelimiter(';');

        // Écriture de l'en-tête du fichier
        $csv->insertOne(['Matricule', 'Code', 'Valeur', 'Date debut', 'Date fin', 'H/J', 'Pourcentage TP']);

        // Écriture des collections dans le fichier CSV
        $csv->insertAll($data[0]);

        // Retourne le chemin du fichier enregistré
        return str_replace('\\', '/', 'http://localhost/evypaie_back/storage/csv/' . $filename . '.csv');
        // return str_replace('\\', '/', 'http://evyplus.preprod.inpact.fr/evyplus/back/storage/csv/' . $filename . '.csv');
    }


    /**
     * Convertit un fichier CSV.
     *
     * @param Reader $file Objet Reader contenant les données du fichier CSV
     * @throws Exception
     */


    public function convertFile(Request $request): JsonResponse
    {
        // Récupération des paramètres de la requête
        $folderId = $request->get('company_folder_id');
        $month = $request->get('month');
        $year = $request->get('year');
        $interfaceId = $request->get('interface_id');

        // Récupération du dossier de la compagnie
        $folder = CompanyFolder::findOrFail($folderId);
        $date = $folder->folder_number . '_' . $month . $year;

        // Récupération de l'interface software
        $interfaceSoftware = InterfaceSoftware::find($interfaceId);

        if (!$interfaceSoftware) {
            return $this->errorResponse('L\'interface n\'existe pas', 404);
        }

        $idSoftware = $interfaceSoftware->interface_mapping_id;
        $data = [];

        if ($idSoftware) {
            // Conversion standard via ConvertInterfaceController
            $controller = new ConvertInterfaceController();
            $data = $controller->convertInterface($request);
        } else {
            // Conversion spécifique en fonction du nom de l'interface
            $softwareName = strtolower($interfaceSoftware->name);
            $data = $this->handleSpecificInterfaceConversion($softwareName, $request);
            if (!$data) {
                return $this->errorResponse('Paramétrage spécifique de l\'interface manquant ou à développer', 400);
            }
        }

        return $this->prepareResponse($data, $date, $folderId);
    }

    private function handleSpecificInterfaceConversion(string $softwareName, Request $request): ?array
    {
        switch ($softwareName) {
            case 'marathon':
                $controller = new ConvertMEController();
                return $controller->marathonConvert($request);

            default:
                return null;
        }
    }

    private function prepareResponse(array $data, string $date, int $folderId): JsonResponse
    {
        $mappedRubrics = $data[0];
        $unmappedRubric = $data[1];

        // Convertir les rubriques non mappées en une collection Laravel
        $unmappedCollection = collect($unmappedRubric);

        // Récupérer les rubriques mappées pour le dossier spécifique
        $mappingRecords = Mapping::where('company_folder_id', $folderId)
            ->get()
            ->pluck('data')
            ->flatten(1);

        // Convertir les rubriques mappées en une collection Laravel
        $mappingCollection = collect($mappingRecords);

        // Extraire les codes des rubriques mappées
        $mappedCodes = $mappingCollection->pluck('input_rubrique')->all();

        // Trouver toutes les rubriques non mappées en évitant les doublons
        $allUnmappedRubrics = $unmappedCollection->filter(function ($item) use ($mappedCodes) {
            return !in_array($item['Code'], $mappedCodes);
        })->unique('Code');

        // Extraire uniquement les codes des rubriques non mappées
        $unmappedCodes = $allUnmappedRubrics->pluck('Code')->all();

        $header = ['Matricule', 'Code', 'Valeur', 'Date debut', 'Date fin'];

        if ($unmappedCodes) {
            return $this->errorConvertResponse('Conversion impossible, les rubriques suivantes ne sont pas mappées :', implode(', ', $unmappedCodes));
        }
        $csvConverted = $this->writeToFile($data, $date);
        return $this->successConvertResponse($mappedRubrics, 'Votre fichier a été converti', $header, $csvConverted);
    }


}
