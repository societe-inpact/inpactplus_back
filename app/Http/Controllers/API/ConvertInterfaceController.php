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

        $colonne_matricule = $columnindex->colonne_matricule -1;
        $colonne_rubrique = $columnindex->colonne_rubrique -1;
        $colonne_valeur = $columnindex->colonne_valeur -1;
        $colonne_datedeb = $columnindex->colonne_datedeb -1;
        $colonne_datefin = $columnindex->colonne_datefin -1;
        $colonne_hj = $columnindex->colonne_hj -1;
        $colonne_pourcentagetp = $columnindex->colonne_pourcentagetp -1;


        // Vérification s'il y a une en-tête en fonction du matricule

        $containsDigit = ctype_digit($records[0][$colonne_matricule]);
        if (($containsDigit) === false) {
            unset($records[0]);
        }

        // Création de la nouvelle table qui correspond à silae

        foreach ($records as $record) {

            $codeSilae = $this->getSilaeCode($record[$colonne_rubrique], $folderId);

            $matricule = $record[$colonne_matricule];
            $valeur = $record[$colonne_valeur];

            // vérification s'il y a une valeur à reprendre

            if ($columnindex->colonne_datedeb === null){
                $datedebut = "";
            }else{
                $datedebut = $record[$colonne_datedeb];
            }

            if ($columnindex->colonne_datefin === null){
                $datefin = "";
            }else{
                $datefin = $record[$colonne_datefin];
            }

            if ($columnindex->colonne_hj === null){
                $hj = "";
            }else{
                $hj = $record[$colonne_hj];
            }

            if ($columnindex->colonne_pourcentagetp === null){
                $pourcentagetp = "";
            }else{
                $pourcentagetp = $record[$colonne_pourcentagetp];
            }

            // création de la table data et non mappée

            if ($codeSilae){
                $data[] = [
                    'Matricule' => $matricule,
                    'Code' => $codeSilae->code,
                    'Valeur' => $valeur,
                    'Date debut' => $datedebut,
                    'Date fin' => $datefin,
                    'HJ' => $hj,
                    'PctTP' => $pourcentagetp,
                ];
            }else{
                $unmapped[] = [
                    'Matricule' => $matricule,
                    'Code' => $record[$colonne_rubrique],
                    'Valeur' => $valeur,
                    'Date debut' => $datedebut,
                    'Date fin' => $datefin,
                    'HJ' => $hj,
                    'PctTP' => $pourcentagetp,
                ];
            }
        }
        // dd($unmapped);
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
        $columnindex = $this->indexColumn($softwareId->interface_software_id);

        $type_separateur = $columnindex->separator_type;
        $format = strtolower($columnindex->extension);

        switch ($format){
            case "csv":
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
                    $reader->setDelimiter($type_separateur);
                    $reader->addFormatter($formatter);
                    $reader->setHeaderOffset(null);
                    $records = iterator_to_array($reader->getRecords(), true);
                    return $this->convertInter($records, $folderId, $columnindex);

                }
            default :
                return $this->errorResponse('Conversion impossible, le format n\'est pas pris en charge', 400);
        }
    }
}
