<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\ConvertController2;
use Illuminate\Http\Request;
use App\Models\Mapping\Mapping;
use Illuminate\Support\Facades\App;
use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\Reader;


class ConvertMEController extends ConvertController
{
    // importer les index des colonnes pour l'extraction

    public function formatFilesMarathon()
    {
        $formatMarathon = [ "format" => "csv" , "separateur" => ";" , "index_rubrique" => 3 ];
        return $formatMarathon;
    }

    public function getMappingsFolder($folderId)
    {
        return Mapping::where('company_folder_id', $folderId)->get();
    }


    public function indexColumn($nominterface)
    {
        $controller = new InterfaceMappingController();
        return $controller->getInterfaceSoftware($nominterface);
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

    const CORRESPONDENCES = [
        'absences' => [
            'J' => 0, // Correspond à une journée d'absence
            'A' => 0.5, // Correspond à une demie-journée d'absence (Après-midi)
            'M' => 0.5, // Correspond à une demie-journée d'absence (Matinée)
        ],
    ];

    /**
     * Convertit les données .
     *
     * @param Reader $file Objet Reader contenant les données du fichier CSV
     */
    private function marathonInterface($records, $folderId): array
    {
        $data = [];
        $unmapped = [];

        // Vérification s'il y a une en-tête en fonction du matricule

        $containsDigit = ctype_digit($records[0][0]);
        if (($containsDigit) === false) {
            unset($records[0]);
        }

        // Création de la nouvelle table qui correspond à silae

        foreach ($records as $record) {

            $codeSilae = $this->getSilaeCode($record[3], $folderId);
            $matricule = $record[0];
            $valeur = $record[4];

            // création de la table data et non mappée

            if ($codeSilae){

                if (str_starts_with($codeSilae->code, "AB-")) {
                    preg_match_all('/(\d{8}J-\d{8}J-\d{3}-\d{2}:\d{2})/i', $valeur, $values);;
                    foreach ($values[0] as $value){
                        preg_match('/((\d{4})(\d{2})(\d{2})([A-Z]))-((\d{4})(\d{2})(\d{2})([A-Z]))-((\d{3})-(\d{2}:\d{2}))/i', $value, $matches);
                        $data = $this->processAbsenceRecord($data, $record, $codeSilae, $matches);
                    }

                } else {
                    $data = $this->convertNegativeOrDotValue($data, $record, $codeSilae);
                }

            }else{
                $unmapped[] = [
                    'Matricule' => $matricule,
                    'Code' => $record[3],
                    'Valeur' => $valeur,
                    'Date debut' => '',
                    'Date fin' => '',
                ];
            }
        }
        // dd($unmapped);
        return [$data, $unmapped];
    }

    private function processAbsenceRecord(array $data, array $record, $codeSilae, array $matches): array
    {
        $value = $this->calculateAbsenceTypePeriod($codeSilae, $matches);
        $start_date = $matches[4] . "/" . $matches[3] . "/" . $matches[2];
        $end_date = $matches[9] . "/" . $matches[8] . "/" . $matches[7];

        if ($matches[5] === 'J' && $matches[10] === 'J') {
            // if ($codeSilae->base_calcul === 'H') {
            //     $data[] = [
            //         'Matricule' => $record[0],
            //         'Code' => $codeSilae->code,
            //         'Valeur' => intval($matches[13]),
            //         'Date debut' => '',
            //         'Date fin' => ''
            //     ];
            // }else
            if ($start_date != $end_date) {
                $data[] = [
                    'Matricule' => $record[0],
                    'Code' => $codeSilae->code,
                    'Valeur' => str($value),
                    'Date debut' => $start_date,
                    'Date fin' => $end_date
                ];
            } else {
                $data = $this->addDateRangeToRecords($data, $record[0], $codeSilae->code, str($value), strtotime(str_replace('/', '-', $start_date)), strtotime(str_replace('/', '-', $end_date)));
            }

        } elseif (($matches[5] === 'A' && $matches[10] === 'A') || ($matches[5] === 'M' && $matches[10] === 'M')) {
            $value = self::CORRESPONDENCES['absences']['A'];
            if ($codeSilae->base_calcul === 'H') {
                $value = intval($matches[13]);
            }

            if (strtotime(str_replace('/', '-', $start_date)) === strtotime(str_replace('/', '-', $end_date))) {
                $data[] = [
                    'Matricule' => $record[0],
                    'Code' => $codeSilae->code,
                    'Valeur' => str($value),
                    'Date debut' => $start_date,
                    'Date fin' => $end_date
                ];
            }
        } elseif ($matches[5] === 'A' && $matches[10] === 'J') {

            if (str_contains($matches[1], 'A')) {
                $value = self::CORRESPONDENCES['absences']['A'];
                $date = $matches[4] . "/" . $matches[3] . "/" . $matches[2];
                $date_str = str_replace('/', '-', $date);
                $date_formatted = strtotime($date_str);
                $data[] = [
                    'Matricule' => $record[0],
                    'Code' => $codeSilae->code,
                    'Valeur' => str($value),
                    'Date debut' => date('d/m/Y', $date_formatted),
                    'Date fin' => date('d/m/Y', $date_formatted)
                ];
            }


            if (str_contains($matches[6], 'J')) {
                $value = self::CORRESPONDENCES['absences']['J'];
                $date = $matches[9] . "/" . $matches[8] . "/" . $matches[7];
                $date_str = str_replace('/', '-', $date);
                $date_formatted = strtotime($date_str);
                $difference = intval($matches[9]) - intval($matches[4]);
                if ($matches[4] < $matches[9] && $difference >= 2) {
                    $data[] = [
                        'Matricule' => $record[0],
                        'Code' => $codeSilae->code,
                        'Valeur' => str($value),
                        'Date debut' => date('d/m/Y', $date_formatted - $difference),
                        'Date fin' => date('d/m/Y', $date_formatted)
                    ];
                } else {
                    $data[] = [
                        'Matricule' => $record[0],
                        'Code' => $codeSilae->code,
                        'Valeur' => str($value),
                        'Date debut' => date('d/m/Y', $date_formatted),
                        'Date fin' => date('d/m/Y', $date_formatted)
                    ];
                }
            }
        }
        return $data;
    }
// TODO : ajouter une condition d'erreur
    // Fonction permettant de convertir les valeurs negatives ou commençant par un point
    private function convertNegativeOrDotValue(array $data, array $record, $codeSilae): array
    {
        $value = $record[4];

        // Si la valeur commence par un ".",
        if (str_starts_with($value, '.')) {
            $value = '0' . $value;
        }

        $data[] = [
            'Matricule' => $record[0],
            'Code' => $codeSilae->code,
            'Valeur' => (float)$value,
            'Date debut' => '',
            'Date fin' => ''
        ];

        return $data;
    }

    // Fonction déterminant si la valeur doit être calculée sur une base heures (H) ou jours (J)
    private function calculateAbsenceTypePeriod($codeSilae, array $matches)
    {
        if ($codeSilae->base_calcul === 'H') {
            return intval($matches[13]);
        }
        return self::CORRESPONDENCES['absences']['J'];
    }

    // Fonction permettant d'ajouter la date de début et de fin
    private function addDateRangeToRecords(array $data, string $matricule, $codeSilae, $value, int $start_date_formatted, int $end_date_formatted): array
    {
        while ($start_date_formatted <= $end_date_formatted) {
            $data[] = [
                'Matricule' => $matricule,
                'Code' => $codeSilae,
                'Valeur' => $value,
                'Date debut' => date('d/m/Y', $start_date_formatted),
                'Date fin' => date('d/m/Y', $start_date_formatted),
            ];
            $start_date_formatted = strtotime('+1 day', $start_date_formatted);
        }

        return $data;
    }


        /**
     * Convertit un fichier CSV à l'aide de la méthode convert et écrit les résultats dans une table.
     *
     * @param Reader $file Objet Reader contenant les données du fichier CSV
     * @throws Exception
     */
    public function marathonConvert(request $request)
    {
        // reprise des différentes informations

        $folderId = $request->get('company_folder_id');
        // extraction en fonction du format => voir pour le sortir dans une autre fonction

        $encoder = (new CharsetConverter())->inputEncoding('iso-8859-15');
        $formatter = fn(array $row): array => array_map('strtoupper', $row);

        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
        ]);

        if ($request->hasFile('csv'))
        {
            $file = $request->file('csv');
            $reader = Reader::createFromPath($file->getPathname(), 'r');
            $reader->addFormatter($encoder);
            $reader->setDelimiter(';');
            $reader->addFormatter($formatter);
            $reader->setHeaderOffset(null);
            $header = $reader->getHeader();
            $records = iterator_to_array($reader->getRecords(), true);
            $result = $this->marathonInterface($records, $folderId);

            return $result;
        }
    }
}
