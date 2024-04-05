<?php

namespace App\Classes\Converters;

use App\Http\Controllers\Controller;
use App\Interfaces\ConverterInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use League\Csv\Writer;
use RuntimeException;
use Illuminate\Support\Facades\Date;
use function Laravel\Prompts\error;
use function PHPUnit\Framework\matches;

class MarathonConverter extends Controller implements ConverterInterface
{
    private $correspondenceGlobalTable;

    const BASE_CALCUL = [
        'AB-142' => 'H',
        'AB-230' => 'H',
        'AB-260' => 'H',
        'AB-263' => 'H',
        'AB-608' => 'H',
        'AB-630' => 'H',
        'AB-631' => 'H',
        'AB-632' => 'H',
        'AB-652' => 'H',
        'EV-NbhRTTPris' => 'H',
        'EV-NbhRTTSolde' => 'H',
        'AB-100' => 'J',
        'AB-110' => 'J',
        'AB-120' => 'J',
        'AB-200' => 'J',
        'AB-210' => 'J',
        'AB-300' => 'J',

    ];

    const CORRESPONDENCES = [
        'absences' => [
            'J' => 0, // Correspond à une journée d'absence
            'A' => 0.5, // Correspond à une demie-journée d'absence (Après-midi)
            'M' => 0.5, // Correspond à une demie-journée d'absence (Matinée)
        ],
    ];

    public function __construct(string $badgeuse = 'marathon')
    {
        // Chargement de la table de correspondance depuis la configuration
        $this->correspondenceGlobalTable = Config::get("mapping.$badgeuse", []);
    }

    /**
     * @throws InvalidArgument
     * @throws UnavailableStream
     * @throws Exception
     */
    public function importFile(Request $request): JsonResponse
    {
        // Formateur pour convertir les valeurs en majuscules
        $encoder = (new CharsetConverter())->inputEncoding('iso-8859-15');
        $formatter = fn(array $row): array => array_map('strtoupper', $row);
        if ($request->hasFile('csv')) {
            $file = $request->file('csv');

            $reader = Reader::createFromPath($file->getPathname(), 'r');
            $reader->addFormatter($encoder);
            $reader->setDelimiter(';');
            $reader->addFormatter($formatter);
            $reader->setHeaderOffset(0);
            $headersArray = $reader->getHeader();
            $jsonArray = [];
            foreach ($headersArray as $header) {
                $jsonArray[] = ["field" => $header];
            }

            // Affichage des en-têtes
            return response()->json([
                'success' => true,
                'message' => 'Votre fichier a été convertit',
                'status' => 200,
                'header' => $jsonArray,
                'data' => $reader->jsonSerialize()
            ]);
            //$this->convertFile($reader);
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
    private function getSilaeCode(string $rubrique): ?string
    {
        // Suppression du préfixe (P, 1, 3) et recherche du code Silae correspondant
        $rubriqueWithoutPrefix = preg_replace('/^([P13])/', '', $rubrique);
        return $this->correspondenceGlobalTable[strtoupper($rubriqueWithoutPrefix)] ?? response()->json('no');
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

        if ($request->hasFile('csv')) {
            $file = $request->file('csv');
            $reader = Reader::createFromPath($file->getPathname(), 'r');
            $reader->addFormatter($encoder);
            $reader->setDelimiter(';');
            $reader->addFormatter($formatter);
            $reader->setHeaderOffset(0);
            $data = $this->convert($reader);
            $csvConverted = $this->writeToFile($data);
            $headersArray = ['Matricule', 'Code', 'Valeur', 'Date debut', 'Date fin'];
            $headerInJson = [];
            foreach ($headersArray as $header) {
                $headerInJson[] = ["field" => $header];
            }
            return response()->json([
                'success' => true,
                'message' => 'Votre fichier a été convertit',
                'status' => 200,
                'file' => $csvConverted,
                'header' => $headerInJson,
                'data' => $data
            ]);
            //$this->convertFile($reader);
        }

        return response()->json([
            'success' => false,
            'message' => 'Veuillez importer un fichier',
            'status' => 400
        ]);
    }


    /**
     * Convertit les données du fichier CSV et retourne les collections converties.
     *
     * @param Reader $file Objet Reader contenant les données du fichier CSV
     */
    private function convert(Reader $file): array
    {
        $data = [];
        $records = iterator_to_array($file, true);
        // TODO : Voir si il y a une façon déjà établie de convertir sans header
        foreach ($records as $record) {
            $codeSilae = $this->getSilaeCode($record['RUBRIQUE']);

            // preg permettant le dégroupement de la date
            preg_match('/((\d{4})(\d{2})(\d{2})([A-Z]))-((\d{4})(\d{2})(\d{2})([A-Z]))-((\d{3})-(\d{2}:\d{2}))/i', $record['MONTANT'], $matches);

            if (str_starts_with($codeSilae, "AB-")) {
                $data = $this->processAbsenceRecord($data, $record, $codeSilae, $matches);
            } elseif (str_starts_with($codeSilae, "EV-") || str_starts_with($codeSilae, "HS-")) {
                $data = $this->convertNegativeOrDotValue($data, $record, $codeSilae);
            }
        }

        // Tri croissant sur le matricule et le code
        usort($data, function ($a, $b) {
            return $a['Matricule'] <=> $b['Matricule'] ?: $a['Code'] <=> $b['Code'];
        });
        return $data;
    }

    private function processAbsenceRecord(array $data, array $record, string $codeSilae, array $matches): array
    {
        if (is_numeric($record['MONTANT'])) {
            return $data;
        }

        $value = $this->calculateAbsenceTypePeriod($codeSilae, $matches);
        $start_date = $matches[4] . "/" . $matches[3] . "/" . $matches[2];
        $end_date = $matches[9] . "/" . $matches[8] . "/" . $matches[7];

        if ($matches[5] === 'J' && $matches[10] === 'J') {
            if (array_key_exists($codeSilae, self::BASE_CALCUL) && self::BASE_CALCUL[$codeSilae] === 'H') {
                $data[] = [
                    'Matricule' => $record['CODE SALARIE'],
                    'Code' => $codeSilae,
                    'Valeur' => intval($matches[13]),
                    'Date debut' => '',
                    'Date fin' => ''
                ];
            }else if ($start_date != $end_date) {
                $data[] = [
                    'Matricule' => $record['CODE SALARIE'],
                    'Code' => $codeSilae,
                    'Valeur' => str($value),
                    'Date debut' => $start_date,
                    'Date fin' => $end_date
                ];
            } else {
                $data = $this->addDateRangeToRecords($data, $record['CODE SALARIE'], $codeSilae, str($value), strtotime(str_replace('/', '-', $start_date)), strtotime(str_replace('/', '-', $end_date)));
            }

        } elseif (($matches[5] === 'A' && $matches[10] === 'A') || ($matches[5] === 'M' && $matches[10] === 'M')) {
            $value = self::CORRESPONDENCES['absences']['A'];
            if (array_key_exists($codeSilae, self::BASE_CALCUL) && self::BASE_CALCUL[$codeSilae] === 'H') {
                $value = intval($matches[13]);
            }

            if (strtotime(str_replace('/', '-', $start_date)) === strtotime(str_replace('/', '-', $end_date))) {
                $data[] = [
                    'Matricule' => $record['CODE SALARIE'],
                    'Code' => $codeSilae,
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
                    'Code' => $codeSilae,
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
                        'Code' => $codeSilae,
                        'Valeur' => str($value),
                        'Date debut' => date('d/m/Y', $date_formatted - $difference),
                        'Date fin' => date('d/m/Y', $date_formatted)
                    ];
                } else {
                    $data[] = [
                        'Matricule' => $record['CODE SALARIE'],
                        'Code' => $codeSilae,
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
    private function convertNegativeOrDotValue(array $data, array $record, string $codeSilae): array
    {
        $value = $record['MONTANT'];

        // Si la valeur commence par un ".",
        if (str_starts_with($value, '.')) {
            $value = '0' . $value;
        }

        $data[] = [
            'Matricule' => $record['CODE SALARIE'],
            'Code' => $codeSilae,
            'Valeur' => (float)$value,
            'Date debut' => '',
            'Date fin' => ''
        ];

        return $data;
    }

    // Fonction déterminant si la valeur doit être calculée sur une base heures (H) ou jours (J)
    private function calculateAbsenceTypePeriod(string $codeSilae, array $matches): int
    {
        if (array_key_exists($codeSilae, self::BASE_CALCUL) && self::BASE_CALCUL[$codeSilae] === 'H') {
            return intval($matches[13]);
        }

        return self::CORRESPONDENCES['absences']['J'];
    }

    // Fonction permettant d'ajouter la date de début et de fin
    private function addDateRangeToRecords(array $data, string $matricule, string $codeSilae, $value, int $start_date_formatted, int $end_date_formatted): array
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
