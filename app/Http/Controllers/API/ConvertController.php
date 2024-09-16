<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mapping\Mapping;
use App\Traits\HistoryResponseTrait;
use App\Traits\JSONResponseTrait;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Controllers\RuntimeException;
use App\Http\Controllers\API\ConvertInterfaceController;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\InterfaceSoftware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Writer;
use function Symfony\Component\String\s;

class ConvertController extends BaseController
{
    use JSONResponseTrait;
    use HistoryResponseTrait;

    public function indexColumn($nominterface)
    {
        $controller = new InterfaceMappingController();
        return $controller->getInterfaceMapping($nominterface);
    }


    public function importFile(Request $request): JsonResponse
    {
        $user = Auth::user();
        $companyFolderId = $request->get('company_folder_id');
        $companyFolder = CompanyFolder::findOrFail($companyFolderId);

        // Récupération et validation de l'interface
        $idInterface = $request->get('interface_id');
        $interfaceSoftware = InterfaceSoftware::find($idInterface);

        if ($interfaceSoftware === null) {
            return $this->errorResponse('L\'interface n\'existe pas', 404);
        }

        $idSoftware = $interfaceSoftware->interface_mapping_id;

        if ($idSoftware === null) {
            $softwaresName = strtolower($interfaceSoftware->name);
            switch ($softwaresName) {
                case "marathon":
                    $controller = new ConvertMEController();
                    $columnindex = $controller->formatFilesMarathon();
                    break;

                default:
                    return response()->json(['success' => false, 'message' => 'Il manque le paramétrage spécifique de l\'interface !', 'status' => 400]);
            }
        } else {
            $idSoftware = InterfaceSoftware::findOrFail($idInterface);
            $columnindex = $this->indexColumn($idSoftware->interface_mapping_id);
        }

        $separator_type = $columnindex['separator_type'];
        $extension = strtolower($columnindex['extension']);

        // Validation du fichier en fonction de l'extension
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xls,xlsx',
        ]);

        if (!$request->hasFile('file')) {
            return $this->errorResponse('Veuillez importer un fichier', 400);
        }

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        // Répertoire pour les fichiers importés
        $importDirectory = storage_path('app/public/');
        $importPath = $importDirectory . '/' . $fileName;

        // Création du dossier s'il n'existe pas
        if (!is_dir($importDirectory)) {
            mkdir($importDirectory, 0777, true);
        }

        // Déplacement du fichier dans le répertoire importé
        $file->move($importDirectory, $fileName);
        return $this->handleFile($importPath, $separator_type);
        // Traiter le fichier en fonction de l'extension
        //return match ($extension) {
        //    'csv' => $this->handleFile($importPath, $separator_type),
        //    'xls' => $this->handleFile($importPath, $separator_type),
        //    default => $this->errorResponse('Le format de fichier ' . $extension . ' n\'est pas supporté')
        //};
    }


    private function handleFile($csvPath, $delimiter): JsonResponse
    {
        $encoder = (new CharsetConverter())->inputEncoding('iso-8859-15')->outputEncoding('UTF-8');
        $extension = fn(array $row): array => array_map('strtoupper', $row);

        // Lire le fichier CSV depuis le chemin où il a été déplacé
        $reader = Reader::createFromPath($csvPath, 'r');
        $reader->addFormatter($encoder);
        $reader->setDelimiter($delimiter);
        $reader->addFormatter($extension);
        $reader->setHeaderOffset(null);

        $records = iterator_to_array($reader->getRecords(), true);

        $relativePath = str_replace(storage_path('app/public') . '/', '', $csvPath);
        $importedFileUrl = url('storage/' . $relativePath);
        if ($records) {
            return $this->successImportedFileResponse($importedFileUrl, $records, 'Votre fichier a bien été importé');
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
        $fileName = 'EVY_' . $date; // TODO : Modifier le nom du fichier

        $directory = storage_path('app/public/');
        $csvPath = $directory . '/' . $fileName . '.csv';

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

        $relativePath = str_replace(storage_path('app/public/'), '', $csvPath);
        return url('storage' . $relativePath);
    }


    /**
     * Convertit un fichier CSV.
     *
     * @param Reader $file Objet Reader contenant les données du fichier CSV
     * @throws Exception
     */


    public function convertFile(Request $request): JsonResponse
    {
        $file = $request->file('file');

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

        return $this->prepareResponse($data, $date, $folderId, $file);
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

    private function prepareResponse(array $data, string $date, int $companyFolderId, $file = null): JsonResponse
    {
        $importedFileName = $file->getClientOriginalName();
        $user = Auth::user();
        $companyFolder = CompanyFolder::findOrFail($companyFolderId);

        $mappedRubrics = $data[0];
        $unmappedRubric = $data[1];

        // Convertir les rubriques non mappées en une collection Laravel
        $unmappedCollection = collect($unmappedRubric);

        // Récupérer les rubriques mappées pour le dossier spécifique
        $mappingRecords = Mapping::where('company_folder_id', $companyFolderId)
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
        $fileConvertedPath = $this->writeToFile($data, $date);
        $date = now()->format('d/m/Y à H:i');
        $directory = storage_path('app/public/');
        $csvPath = $directory . '/' . $importedFileName;

        $relativePath = str_replace(storage_path('app/public/'), '', $csvPath);
        $this->setConvertHistory('Conversion', $user, $companyFolderId, 'convert', $date, $importedFileName, basename($fileConvertedPath), url('storage' . $relativePath), $fileConvertedPath, '', '');

        return $this->successConvertResponse($mappedRubrics, 'Votre fichier a été converti', $header, $fileConvertedPath);
    }

}
