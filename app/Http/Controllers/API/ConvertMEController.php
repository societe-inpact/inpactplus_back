<?php

namespace App\Http\Controllers\API;

use App\Classes\Rubric;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use App\Models\Mapping\Mapping;
use Illuminate\Support\Facades\App;
use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\Reader;


class ConvertMEController extends ConvertController
{
    use JSONResponseTrait;

    const CORRESPONDENCES = [
        'absences' => [
            'J' => 0, // Correspond à une journée d'absence
            'A' => 0.5, // Correspond à une demie-journée d'absence (Après-midi)
            'M' => 0.5, // Correspond à une demie-journée d'absence (Matinée)
        ],
    ];

    public function formatFilesMarathon()
    {
        return ["extension" => "csv", "separator_type" => ";"];
    }

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


    private function getSilaeCode(string $rubrique, $folderId)
    {
        $mappings = $this->getMappingsFolder($folderId);
        foreach ($mappings as $mapping) {
            foreach ($mapping->data as $mappedRow) {
                $mappedRubric = new Rubric($mappedRow);
                if ($mappedRow['input_rubrique'] === $rubrique && $mappedRow['is_used']) {
                    return $mappedRubric->getSilaeRubric();
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
    private function marathonInterface($records, $folderId): array
    {
        $mappedrubrics = [];
        $unmappedRubrics = [];

        // Vérification s'il y a une en-tête en fonction du matricule
        $containsDigit = ctype_digit($records[0][0]);
        if (($containsDigit) === false) {
            unset($records[0]);
        }

        // Création de la nouvelle table qui correspond à silae
        foreach ($records as $record) {
            $codeSilae = $this->getSilaeCode($record[3], $folderId);
            $matricule = $record[0];
            $value = $record[4];
            if ($codeSilae) {
                preg_match('/((\d{4})(\d{2})(\d{2})([A-Z]))-((\d{4})(\d{2})(\d{2})([A-Z]))-((\d{3})-(\d{2}:\d{2}))/i', $value, $matches);
                if (str_starts_with($codeSilae->code, "AB-")) {
                    // Cas où l'absence contient un montant en int comme valeur
                    if (is_numeric($value)) {
                        $mappedrubrics[] = [
                            'Matricule' => $matricule,
                            'Code' => $codeSilae->code,
                            'Valeur' => $value,
                            'Date debut' => '',
                            'Date fin' => ''
                        ];
                    } else {
                        // Cas où l'absence contient une date comme valeur
                        $mappedrubrics = $this->processAbsenceRecord($mappedrubrics, $record, $codeSilae, $value);
                    }
                } elseif (str_starts_with($codeSilae->code, "EV-") || str_starts_with($codeSilae->code, "HS-")) {
                    $mappedrubrics = $this->convertNegativeOrDotValue($mappedrubrics, $record, $codeSilae);
                }
            } else {
                $unmappedRubrics[] = [
                    'Matricule' => $matricule,
                    'Code' => $record[3],
                    'Valeur' => $value,
                    'Date debut' => '',
                    'Date fin' => '',
                ];
            }
        }
        return [$mappedrubrics, $unmappedRubrics];
    }

    private function processAbsenceRecord(array $data, array $record, $codeSilae, $value): array
    {
        // Vérification si le montant contient des pipes (plusieurs dates)
        if (str_contains($value, '|')) {

            // Séparation des dates
            $dates = explode('|', $value);
            foreach ($dates as $date) {
                // Traitement de chaque date comme valeur individuelle
                $data = $this->processSingleAbsenceRecord($data, $record, $codeSilae, $date);
            }
        } else {
            // Traitement de la date seule
            $data = $this->processSingleAbsenceRecord($data, $record, $codeSilae, $value);
        }
        return $data;
    }


    private function processSingleAbsenceRecord(array $data, array $record, $codeSilae, $value): array
    {
        preg_match('/((\d{4})(\d{2})(\d{2})([A-Z]))-((\d{4})(\d{2})(\d{2})([A-Z]))-((\d{3})-(\d{2}:\d{2}))/i', $value, $matches);
        $start_date = $matches[4] . "/" . $matches[3] . "/" . $matches[2];
        $end_date = $matches[9] . "/" . $matches[8] . "/" . $matches[7];

        $periodType = $this->calculateAbsenceTypePeriod($codeSilae, $matches);

        if ($matches[5] === 'J' && $matches[10] === 'J') {
            if ($start_date != $end_date) {
                $data[] = [
                    'Matricule' => $record[0],
                    'Code' => $codeSilae->code,
                    'Valeur' => str($periodType),
                    'Date debut' => $start_date,
                    'Date fin' => $end_date
                ];
            } else {
                $data = $this->addDateRangeToRecords($data, $record[0], $codeSilae->code, str($periodType), strtotime(str_replace('/', '-', $start_date)), strtotime(str_replace('/', '-', $end_date)));
            }

        } elseif (($matches[5] === 'A' && $matches[10] === 'A') || ($matches[5] === 'M' && $matches[10] === 'M')) {
            $periodType = self::CORRESPONDENCES['absences']['A'];
            if ($codeSilae->base_calcul === 'H') {
                $periodType = intval($matches[13]);
            }

            if (strtotime(str_replace('/', '-', $start_date)) === strtotime(str_replace('/', '-', $end_date))) {
                $data[] = [
                    'Matricule' => $record[0],
                    'Code' => $codeSilae->code,
                    'Valeur' => str($periodType),
                    'Date debut' => $start_date,
                    'Date fin' => $end_date
                ];
            }
        } elseif ($matches[5] === 'A' && $matches[10] === 'J') {
            if (str_contains($matches[1], 'A')) {
                $periodType = self::CORRESPONDENCES['absences']['A'];
                $date = $matches[4] . "/" . $matches[3] . "/" . $matches[2];
                $date_str = str_replace('/', '-', $date);
                $date_formatted = strtotime($date_str);
                $data[] = [
                    'Matricule' => $record[0],
                    'Code' => $codeSilae->code,
                    'Valeur' => str($periodType),
                    'Date debut' => date('d/m/Y', $date_formatted),
                    'Date fin' => date('d/m/Y', $date_formatted)
                ];
            }

            if (str_contains($matches[6], 'J')) {
                $periodType = self::CORRESPONDENCES['absences']['J'];
                $date = $matches[9] . "/" . $matches[8] . "/" . $matches[7];
                $date_str = str_replace('/', '-', $date);
                $date_formatted = strtotime($date_str);
                $difference = intval($matches[9]) - intval($matches[4]);
                if ($matches[4] < $matches[9] && $difference >= 2) {
                    $data[] = [
                        'Matricule' => $record[0],
                        'Code' => $codeSilae->code,
                        'Valeur' => str($periodType),
                        'Date debut' => date('d/m/Y', $date_formatted - $difference),
                        'Date fin' => date('d/m/Y', $date_formatted)
                    ];
                } else {
                    $data[] = [
                        'Matricule' => $record[0],
                        'Code' => $codeSilae->code,
                        'Valeur' => str($periodType),
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

        if ($request->hasFile('csv')) {
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
