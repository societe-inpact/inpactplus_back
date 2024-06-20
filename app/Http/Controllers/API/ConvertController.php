<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RuntimeException;
use App\Models\Mapping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use League\Csv\Writer;
use function Laravel\Prompts\error;
use function PHPUnit\Framework\isEmpty;

class ConvertController extends Controller
{
    const CORRESPONDENCES = [
        'absences' => [
            'J' => 0, // Correspond à une journée d'absence
            'A' => 0.5, // Correspond à une demie-journée d'absence (Après-midi)
            'M' => 0.5, // Correspond à une demie-journée d'absence (Matinée)
        ],
    ];


    /**
     * @throws InvalidArgument
     * @throws UnavailableStream
     * @throws Exception
     */
    public function importFile(Request $request): JsonResponse
    {
        $encoder = (new CharsetConverter())->inputEncoding('iso-8859-15');
        $formatter = fn(array $row): array => array_map('strtoupper', $row);

        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
        ]);


        if ($request->hasFile('csv')) {
            $file = $request->file('csv');
            $reader = Reader::createFromPath($file->getPathname(), 'r');
            $reader->addFormatter($encoder);
            $reader->setDelimiter(';');
            $reader->addFormatter($formatter);
            $reader->setHeaderOffset(0);
            $header = $reader->getHeader();
            $records = $reader->getRecords();
            $data = [];

// Vérifiez si le header contient des chiffres
            $containsDigit = false;
            foreach ($header as $column) {
                for ($i = 0; $i < strlen($column); $i++) {
                    if (is_numeric($column[$i])) {
                        $containsDigit = true;
                        break 2; // Utiliser break 2 pour sortir des deux boucles
                    }
                }
            }

// Traitez les records en fonction de la vérification du header
            if ($containsDigit) {
                $header = array_map(function($index) {
                    return $index;
                }, array_keys($header));
                // Dans le cas où le fichier ne contient pas de header
                foreach ($records as $record) {
                    $mappedRecord = [];
                    foreach (array_values($record) as $index => $value) {
                        $mappedRecord[$index] = $value;
                    }
                    $data[] = $mappedRecord;
                }
            } else {
                foreach ($records as $record) {
                    $mappedRecord = [];
                    foreach ($header as $columnName) {
                        if (array_key_exists($columnName, $record)) {
                            $mappedRecord[$columnName] = $record[$columnName];
                        } else {
                            $mappedRecord[$columnName] = null;
                        }
                    }
                    $data[] = $mappedRecord;
                }
            }


            return response()->json([
                'success' => true,
                'message' => 'Votre fichier a été importé',
                'status' => 200,
                'header' => $header,
                'rows' => $data
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Veuillez importer un fichier',
            'status' => 400
        ]);
    }


    /**
     * Écrit les collections dans un fichier CSV.
     *
     * @param array $data Collections converties
     *
     * @throws Exception
     * @throws RuntimeException
     */
    private function writeToFile($data)
    {
        $filename = 'ME_' . Date::now()->format('dmY');
        $directory = storage_path('csv');
        $csvPath = $directory . '/' . $filename . '.csv';

        // Create the directory if it doesn't exist
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $csv = Writer::createFromPath($csvPath, 'w+');
        $csv->setDelimiter(';');

        // Écriture de l'en-tête du fichier
        $csv->insertOne(['Matricule', 'Code', 'Valeur', 'Date debut', 'Date fin']);

        // Écriture des collections dans le fichier CSV
        $csv->insertAll($data);

        // Return the URL of the CSV file
        return str_replace('\\', '/', 'http://localhost/evypaie_back/storage/csv/' . $filename . '.csv');
    }
    /**
     * Convertit un fichier CSV à l'aide de la méthode convert et écrit les résultats dans un nouveau fichier.
     *
     * @param Reader $file Objet Reader contenant les données du fichier CSV
     * @throws Exception
     */
    public function convertFile(Request $request): JsonResponse
    {
        $encoder = (new CharsetConverter())->inputEncoding('iso-8859-15');
        $formatter = fn(array $row): array => array_map('strtoupper', $row);
        $folderId = $request->get('company_folder_id');
        if ($request->hasFile('csv')) {
            $file = $request->file('csv');
            $reader = Reader::createFromPath($file->getPathname(), 'r');
            $reader->addFormatter($encoder);
            $reader->setDelimiter(';');
            $reader->addFormatter($formatter);
            $reader->setHeaderOffset(0);

            $result = $this->convert($reader, $folderId);
            $data = $result['data'];
            $unmappedRubriques = $result['unmappedRubriques'];
            $header = ['Matricule', 'Code', 'Valeur', 'Date debut', 'Date fin'];

            if (!empty($data)) {
                $csvConverted = $this->writeToFile($data);
                return response()->json([
                    'success' => true,
                    'message' => 'Votre fichier a été converti',
                    'status' => 200,
                    'file' => $csvConverted,
                    'header' => $header,
                    'rows' => $data,
                ]);
            } else {
                $unmappedRubriquesString = implode(', ', $unmappedRubriques);
                return response()->json([
                    'success' => false,
                    'message' => 'Les rubriques suivantes ne sont pas mappées : ' . $unmappedRubriquesString,
                    'status' => 400,
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Veuillez importer un fichier',
            'status' => 400
        ]);
    }



    /**
     * Retourne le code Silae correspondant à une rubrique donnée.
     *
     * @param string $rubrique Rubrique à convertir
     * @return string|null Code Silae correspondant
     */

    private function getMappingsFolder($folderId){
        return Mapping::where('company_folder_id', $folderId)->get();
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


    protected function resolveOutputModel($outputType, $outputRubriqueId)
    {
        if (!class_exists($outputType)) {
            return null;
        }

        $outputModelClass = App::make($outputType);
        return $outputModelClass->find($outputRubriqueId);
    }

    /**
     * Convertit les données du fichier CSV et retourne les collections converties.
     *
     * @param Reader $file Objet Reader contenant les données du fichier CSV
     */
    private function convert(Reader $file, $folderId): array
    {
        $data = [];
        $unmappedRubriques = [];
        $records = iterator_to_array($file, true);
        foreach ($records as $record) {
            $mappedRecord = [];
            foreach (array_values($record) as $index => $value) {
                // Colonne Montant
                if (str_contains($value, '.') || preg_match('/^\d{8}[A-Z]-\d{8}[A-Z]-\d{3}-\d{2}:\d{2}(\|\d{8}[A-Z]-\d{8}[A-Z]-\d{3}-\d{2}:\d{2})?$/', $value)) {
                    $mappedRecord['MONTANT'] = $value;
                } else {
                    $mappedRecord['RUBRIQUE'] = $value;
                }
                // Colonne Code salarie/Matricule
                if ($index === 0 && is_numeric($value) && !str_contains($value, '.')) {
                    $mappedRecord['CODE SALARIE'] = $value;
                }
            }
            // Utiliser les nouveaux noms de colonnes pour accéder aux valeurs
            $codeSilae = $this->getSilaeCode($mappedRecord['RUBRIQUE'], $folderId);
            // preg permettant le dégroupement de la date
            preg_match('/((\d{4})(\d{2})(\d{2})([A-Z]))-((\d{4})(\d{2})(\d{2})([A-Z]))-((\d{3})-(\d{2}:\d{2}))/i', $mappedRecord['MONTANT'], $matches);
            if ($codeSilae) {
                if (str_starts_with($codeSilae->code, "AB-")) {
                    $data = $this->processAbsenceRecord($data, $mappedRecord, $codeSilae, $matches);
                } elseif (str_starts_with($codeSilae->code, "EV-") || str_starts_with($codeSilae->code, "HS-")) {
                    $data = $this->convertNegativeOrDotValue($data, $mappedRecord, $codeSilae);
                }
            } else {
                $unmappedRubriques[] = $mappedRecord['RUBRIQUE'];
            }
        }

        // Tri croissant sur le matricule et le code
        usort($data, function ($a, $b) {
            return $a['Matricule'] <=> $b['Matricule'] ?: $a['Code'] <=> $b['Code'];
        });

        return [
            'data' => $data,
            'unmappedRubriques' => array_unique($unmappedRubriques) // Pour éviter les doublons
        ];
    }



    private function processAbsenceRecord(array $data, array $record, $codeSilae, array $matches): array
    {

        $value = $this->calculateAbsenceTypePeriod($codeSilae, $matches);
        $start_date = $matches[4] . "/" . $matches[3] . "/" . $matches[2];
        $end_date = $matches[9] . "/" . $matches[8] . "/" . $matches[7];



        if ($matches[5] === 'J' && $matches[10] === 'J') {
            if ($codeSilae->base_calcul === 'H') {
                $data[] = [
                    'Matricule' => $record['CODE SALARIE'],
                    'Code' => $codeSilae->code,
                    'Valeur' => intval($matches[13]),
                    'Date debut' => '',
                    'Date fin' => ''
                ];
            }else if ($start_date != $end_date) {
                $data[] = [
                    'Matricule' => $record['CODE SALARIE'],
                    'Code' => $codeSilae->code,
                    'Valeur' => str($value),
                    'Date debut' => $start_date,
                    'Date fin' => $end_date
                ];
            } else {
                $data = $this->addDateRangeToRecords($data, $record['CODE SALARIE'], $codeSilae->code, str($value), strtotime(str_replace('/', '-', $start_date)), strtotime(str_replace('/', '-', $end_date)));
            }

        } elseif (($matches[5] === 'A' && $matches[10] === 'A') || ($matches[5] === 'M' && $matches[10] === 'M')) {
            $value = self::CORRESPONDENCES['absences']['A'];
            if ($codeSilae->base_calcul === 'H') {
                $value = intval($matches[13]);
            }

            if (strtotime(str_replace('/', '-', $start_date)) === strtotime(str_replace('/', '-', $end_date))) {
                $data[] = [
                    'Matricule' => $record['CODE SALARIE'],
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
                    'Matricule' => $record['CODE SALARIE'],
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
                        'Matricule' => $record['CODE SALARIE'],
                        'Code' => $codeSilae->code,
                        'Valeur' => str($value),
                        'Date debut' => date('d/m/Y', $date_formatted - $difference),
                        'Date fin' => date('d/m/Y', $date_formatted)
                    ];
                } else {
                    $data[] = [
                        'Matricule' => $record['CODE SALARIE'],
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

    // Fonction permettant de convertir les valeurs negatives ou commençant par un point
    private function convertNegativeOrDotValue(array $data, array $record, $codeSilae): array
    {
        $value = $record['MONTANT'];

        // Si la valeur commence par un ".",
        if (str_starts_with($value, '.')) {
            $value = '0' . $value;
        }

        $data[] = [
            'Matricule' => $record['CODE SALARIE'],
            'Code' => $codeSilae->code,
            'Valeur' => (float)$value,
            'Date debut' => '',
            'Date fin' => ''
        ];

        return $data;
    }

    // Fonction déterminant si la valeur doit être calculée sur une base heures (H) ou jours (J)
    private function calculateAbsenceTypePeriod($codeSilae, array $matches): int
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
}
