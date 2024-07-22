<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Controllers\RuntimeException;
use App\Http\Controllers\API\ConvertInterfaceController;
use App\Models\Mapping;
use App\Models\CompanyFolder;
use App\Models\Software;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use League\Csv\Writer;

class ConvertController extends BaseController
{
    public function indexColumn($nominterface)
    {
        $controller = new InterfaceSoftwareController();
        return $controller->getInterfaceSoftware($nominterface);
    }

    public function importFile(Request $request): JsonResponse
    {
        // reprise des différentes informations en fonction de l'interface

        $interface = $request->get('nom_interface');
        $nominterface = ['nomInterface' => $interface,];

        $softwaresNames = Software::all()->where('name',$interface)->first();
        $idSoftware = $softwaresNames->interface_software_id;
        if ($idSoftware !== null){
            $columnindex = $this->indexColumn($nominterface);
            // $columnindex = $this->getInterfaceSoftware($nominterface);
            $type_separateur = $columnindex->type_separateur;
            $format = $columnindex->format; 
        }else{
            // mettre en palce la recherche des interfaces spécifique
        }

        // extraction en fonction du format => voir pour le sortir dans une autre fonction
        switch ($format){
            case "csv":
                $encoder = (new CharsetConverter())->inputEncoding('iso-8859-15');
                $formatter = fn(array $row): array => array_map('strtoupper', $row);

                $request->validate([
                    'csv' => 'required|file|mimes:csv,txt',
                ]);
        
                if ($request->hasFile('csv')) {
                    $file = $request->file('csv');
                    $reader = Reader::createFromPath($file->getPathname(), 'r');
                    $reader->addFormatter($encoder);
                    $reader->setDelimiter($type_separateur);
                    $reader->addFormatter($formatter);
                    $reader->setHeaderOffset(null);
                    $header = $reader->getHeader();
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
        $csv->insertAll($data);

        // Return the URL of the CSV file
        return str_replace('\\', '/', 'http://localhost/evypaie_back/storage/csv/' . $filename . '.csv');
    }

    public function convertinterface2($request)
    {
        $controller = new ConvertInterfaceController();
        return $controller->convertinterface($request);
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
        $date = $folderNumber-> folder_number ; //. '_' . $month . $year

        // executer le convert adéquat à l'interface

        $data = [];
        $interface = $request->get('nom_interface');
        $nominterface = ['nomInterface' => $interface,];

        $softwaresNames = Software::all()->where('name',$interface)->first();
        $idSoftware = $softwaresNames->interface_software_id;

        if ($idSoftware !== null){
            $data = $this->convertinterface2($request);
            // $colonne_periode = $columnindex->colonne_periode -1;
        }else{
            // mettre en palce la recherche des interfaces spécifique
        }

        

        if (!empty($data)) {
            $csvConverted = $this->writeToFile($data, $date);
            return response()->json([
                'success' => true,
                'message' => 'Votre fichier a été converti',
                'status' => 200,
                'file' => $csvConverted,
                'rows' => $data,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Conversion impossible, les rubriques suivantes ne sont pas mappées : ',
                'status' => 400,
            ]);
        }
    }

}