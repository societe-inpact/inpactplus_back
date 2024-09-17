<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\ConvertController2;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use App\Models\Mapping\Mapping;
use App\Models\Misc\InterfaceSoftware;
use App\Models\Misc\InterfaceMapping;
use Illuminate\Support\Facades\App;
use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;


class ConvertInterfaceController extends ConvertController
{

    use JSONResponseTrait;

    // importer les index des colonnes pour l'extraction

    public function getMappingsFolder($folderId)
    {
        return Mapping::where('company_folder_id', $folderId)->get();
    }


    public function indexColumn($nominterface)
    {
        $controller = new InterfaceMappingController();
        return $controller->getInterfaceMapping($nominterface);
    }

    /**
     * Retourne le code Silae correspondant à une rubrique donnée.
     *
     * @param string $rubrique Rubrique à convertir
     * @return string|null Code Silae correspondant
     */

    protected function resolveOutputModel($outputType, $outputRubriqueId)
    {
        if (!class_exists($outputType)) {
            return null;
        }

        $outputModelClass = App::make($outputType);
        return $outputModelClass->find($outputRubriqueId);
    }

    private function getSilaeCode(string $rubrique, $folderId)
    {
        $mappings = $this->getMappingsFolder($folderId);
        foreach ($mappings as $mapping){
            foreach ($mapping->data as $mappedRow){
                $output = $this->resolveOutputModel($mappedRow['output_type'], $mappedRow['output_rubrique_id']);
                if ($mappedRow['input_rubrique'] === $rubrique){
                    return $output;
                }
            }
        }
        return null;
    }

    /**
     * Convertit les données .
     *
     * @param Reader $file Objet Reader contenant les données du fichier CSV
     */
    private function convertInter($records, $folderId, $columnindex): array
    {
        $data = [];
        $unmapped = [];

        $employeeNumberColumn = $columnindex->employee_number -1;
        $rubricColumn = $columnindex->rubric -1;
        $valueColumn = $columnindex->value -1;
        $startDateColumn = $columnindex->start_date -1;
        $endDateColumn = $columnindex->end_date -1;
        $hjColumn = $columnindex->hj -1;
        $percentageTPColumn = $columnindex->percentage_tp -1;


        // Vérification s'il y a une en-tête en fonction du matricule

        $containsDigit = ctype_digit($records[0][$employeeNumberColumn]);
        if (($containsDigit) === false) {
            unset($records[0]);
        }

        // Création de la nouvelle table qui correspond à silae
        foreach ($records as $record) {
            $record[$rubricColumn] = utf8_decode($record[$rubricColumn]);
            $codeSilae = $this->getSilaeCode($record[$rubricColumn], $folderId);

            $matricule = $record[$employeeNumberColumn];
            $valeur = $record[$valueColumn];

            // Vérification s'il y a une valeur à reprendre
            $start_date = $columnindex->start_date === null ? "" : $record[$startDateColumn];
            $end_date = $columnindex->end_date === null ? "" : $record[$endDateColumn];
            $hj = $columnindex->hj === null ? "" : $record[$hjColumn];
            $percentage_tp = $columnindex->percentage_tp === null ? "" : $record[$percentageTPColumn];

            // création de la table data et non mappée

            if ($codeSilae){
                $data[] = [
                    'Matricule' => $matricule,
                    'Code' => $codeSilae->code,
                    'Valeur' => $valeur,
                    'Date debut' => $start_date,
                    'Date fin' => $end_date,
                    'HJ' => $hj,
                    'PctTP' => $percentage_tp,
                ];
            }else{
                $unmapped[] = [
                    'Matricule' => $matricule,
                    'Code' => $record[$rubricColumn],
                    'Valeur' => $valeur,
                    'Date debut' => $start_date,
                    'Date fin' => $end_date,
                    'HJ' => $hj,
                    'PctTP' => $percentage_tp,
                ];
            }
        }
        return [$data, $unmapped];
    }

        /**
     * Convertit un fichier CSV à l'aide de la méthode convert et écrit les résultats dans une table.
     *
     * @param Reader $file Objet Reader contenant les données du fichier CSV
     * @throws Exception
     */
    public function convertinterface(request $request)
    {
        // reprise des différentes informations

        $folderId = $request->get('company_folder_id');
        $idsoftware = $request->get('interface_id');

        $softwareId = InterfaceSoftware::findOrFail($idsoftware);
        $columnindex = $this->indexColumn($softwareId->interface_mapping_id);

        $type_separateur = $columnindex->separator_type;
        $format = strtolower($columnindex->extension);
        $encoder = (new CharsetConverter())->inputEncoding('iso-8859-15')->outputEncoding('utf-8');
        switch ($format){
            case "csv":
                $request->validate([
                    'csv' => 'required|file|mimes:csv,txt',
                ]);

                if ($request->hasFile('file'))
                {
                    $file = $request->file('file');
                    $reader = Reader::createFromPath($file->getPathname(), 'r');
                    $reader->addFormatter($encoder);
                    $reader->setDelimiter($type_separateur);
                    $reader->setHeaderOffset(null);
                    $records = iterator_to_array($reader->getRecords(), true);
                    return $this->convertInter($records, $folderId, $columnindex);

                }
                break;
            case "txt":
                $request->validate([
                    'txt' => 'required|file|mimes:txt',
                ]);

                if ($request->hasFile('file'))
                {
                    $file = $request->file('txt');
                    $reader = Reader::createFromPath($file->getPathname(), 'r');
                    $reader->addFormatter($encoder);
                    $reader->setDelimiter($type_separateur);
                    $reader->setHeaderOffset(null);
                    $records = iterator_to_array($reader->getRecords(), true);
                    return $this->convertInter($records, $folderId, $columnindex);

                }
                break;
            case "xls":
                $request->validate([
                    'xls' => 'required|file|mimes:xls',
                ]);

                if ($request->hasFile('file'))
                {
                    $file = $request->file('file');
                    $reader = Reader::createFromPath($file->getPathname(), 'r');
                    $reader->addFormatter($encoder);
                    $reader->setDelimiter($type_separateur);
                    $reader->setHeaderOffset(null);
                    $records = iterator_to_array($reader->getRecords(), true);
                    return $this->convertInter($records, $folderId, $columnindex);

                }
                break;
            case "xlsx" :
                $request->validate([
                    'file' => 'required|file|mimes:xlsx',
                ]);


                if ($request->hasFile('file'))
                {
                    $file = $request->file('file');
                    $spreadsheet = IOFactory::load($file); // Charge le fichier
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = [];

                    foreach ($worksheet->getRowIterator() as $row) {
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false); // Inclure les cellules vides
                        $rowData = [];

                        foreach ($cellIterator as $cell) {
                            $rowData[] = $cell->getValue(); // Récupère les valeurs des cellules
                        }

                        $rows[] = $rowData; // Ajoute chaque ligne au tableau
                    }
                    return $this->convertInter($rows, $folderId, $columnindex);
                }
                break;
        }
        return $this->errorResponse('Conversion impossible, le format n\'est pas pris en charge', 400);
    }
}
