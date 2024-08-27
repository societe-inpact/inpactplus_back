<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
    public function indexColumn($nominterface)
    {
        $controller = new InterfaceMappingController();
        return $controller->getInterfaceMapping($nominterface);
    }

    public function importFile(Request $request): JsonResponse
    {
        // reprise des différentes informations en fonction de l'interface

        $idInterface = $request->get('interface_id');
        $softwaresNames = InterfaceSoftware::all()->where('id',$idInterface)->first();
        if ($softwaresNames !== null){
            $idSoftware = $softwaresNames->interface_software_id;
        }else{
            return response()->json(['message' => 'L\'interface n\'existe pas','status' => 400]);
        }

        if ($idSoftware !== null){
            $idsoftware = InterfaceSoftware::findOrFail($idInterface);
            $columnindex = $this->indexColumn($idsoftware->interface_software_id);
            $type_separateur = $columnindex->separator_type;
            $extension = $columnindex->extension;
        }else{
            $softwaresName = strtolower($softwaresNames["name"]);
            switch ($softwaresName){
                case "marathon":
                    $controller = new ConvertMEController();
                    $columnindex = $controller->formatFilesMarathon();
                    $type_separateur = $columnindex["separator_type"];
                    $extension = $columnindex ["extension"];
                    break;

                default:
                    return response()->json(['success' => false, 'message' => 'il manque le paramétrage spécifique se l\'interface !','status' => 400]);

            }
        }

        // extraction en fonction du format => voir pour le sortir dans une autre fonction
        $extension = strtolower($extension);
        switch ($extension){
            case "csv":
                $encoder = (new CharsetConverter())->inputEncoding('iso-8859-15');
                $extensionter = fn(array $row): array => array_map('strtoupper', $row);

                $request->validate([
                    'csv' => 'required|file|mimes:csv,txt',
                ]);

                if ($request->hasFile('csv')) {
                    $file = $request->file('csv');
                    $reader = Reader::createFromPath($file->getPathname(), 'r');
                    $reader->addFormatter($encoder);
                    $reader->setDelimiter($type_separateur);
                    $reader->addFormatter($extensionter);
                    $reader->setHeaderOffset(null);
                    $records = iterator_to_array($reader->getRecords(), true);

                    return response()->json([
                        'success' => true,
                        'message' => 'Votre fichier a été importé',
                        'status' => 200,
                        'rows' => $records
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Veuillez importer un fichier',
                        'status' => 400
                    ]);
                }
                break;

            default :
                return response()->json(['message' => 'il manque le format suivant', 'format' => $extension ,'status' => 400]);
        }
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
        // dd($data);
        $filename = 'EVY_' . $date; // TODO : Modifier le nom du fichier
        $directory = storage_path('csv');
        $csvPath = $directory . '/' . $filename . '.csv';

        // Create the directory if it doesn't exist
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $csv = Writer::createFromPath($csvPath, 'w+');
        $csv->setDelimiter(';');

        // Écriture de l'en-tête du fichier
        $csv->insertOne(['Matricule', 'Code', 'Valeur', 'Date debut', 'Date fin' , 'H/J' , 'Pourcentage TP']);

        // Écriture des collections dans le fichier CSV
        $csv->insertAll($data[0]);

        // Return the URL of the CSV file
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
        $folderId = $request->get('company_folder_id');
        $folderNumber = CompanyFolder::findOrFail($folderId);
        $month = $request->get('month');
        $year = $request->get('year');
        $date = $folderNumber-> folder_number . '_' . $month . $year;
        $interface = $request->get('interface_id');

        $softwaresNames = InterfaceSoftware::all()->where('id', $interface)->first();
        if ($softwaresNames !== null){
            $idSoftware = $softwaresNames->interface_software_id;
        }else{
            return response()->json(['message' => 'L\'interface n\'existe pas','status' => 400]);
        }


        if ($idSoftware !== null){
            $controller = new ConvertInterfaceController();
            $data =  $controller->convertinterface($request);

        }else{
            // interfaces spécifique
            $softwaresName = strtolower($softwaresNames["name"]);
            switch ($softwaresName){
                case "maratest":
                case "marathon":
                    $controller = new ConvertMEController();
                    $data = $controller->marathonConvert($request);
                    break;
                case "sirh":
                case "rhis":
                    return response()->json(['success' => true, 'message' => 'Algo de l\'interface à développer pour permettre la conversion','status' => 200]);
                default:
                    return response()->json(['success' => false, 'message' => 'il manque le paramétrage spécifique se l\'interface !','status' => 400]);

            }
        }
        $mappedRubrics = $data[0];
        $unmappedRubric = $data[1];
        $collection = collect($unmappedRubric);
        $uniqueUnmappedRubric = $collection->where('is_used', true)
            ->unique(function ($item) {
                return $item['Code'];
            })->values()->all();


        $header = ['Matricule', 'Code', 'Valeur', 'Date debut', 'Date fin'];
        if ($mappedRubrics && empty($uniqueUnmappedRubric)) {
            $csvConverted = $this->writeToFile($data, $date);
            return response()->json([
                'success' => true,
                'message' => 'Votre fichier a été converti',
                'status' => 200,
                'file' => $csvConverted,
                'rows' => $mappedRubrics,
                'header' => $header
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Conversion impossible, les rubriques suivantes ne sont pas mappées : ',
                'unmapped' => $uniqueUnmappedRubric,
                'status' => 400,
            ]);
        }
    }

}
