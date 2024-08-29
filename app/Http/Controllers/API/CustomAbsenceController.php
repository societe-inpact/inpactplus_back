<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absences\Absence;
use App\Models\Absences\CustomAbsence;
use App\Rules\CustomRubricRule;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;

class CustomAbsenceController extends Controller
{
    use JSONResponseTrait;

    public function getCustomAbsences()
    {
        $customAbsences = CustomAbsence::all();
        return response()->json($customAbsences, 200);
    }

    public function createCustomAbsence(Request $request)
    {

        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
            'base_calcul' => 'required|string|max:255',
            'company_folder_id' => 'required|integer',
            'therapeutic_part_time' => 'nullable|boolean',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        // Vérifie si la custom absence avec ce code et ce label existe déjà
        $isCustomAbsenceExists = CustomAbsence::where('company_folder_id', $validated['company_folder_id'])
            ->where('code', $validated['code'])
            ->where('label', $validated['label'])
            ->where('base_calcul', $validated['base_calcul'])
            ->exists();


        $isAbsenceExists = Absence::where('code', $validated['code'])
            ->where('base_calcul', $validated['base_calcul'])
            ->exists();


        if ($isCustomAbsenceExists || $isAbsenceExists) {
            return response()->json(['message' => 'Absence déjà existante.'], 400);
        }

        if (str_starts_with($validated['code'], 'AB-')) {
            // Création de l'absence personnalisée
            $customAbsence = CustomAbsence::create([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'base_calcul' => $validated['base_calcul'],
                'company_folder_id' => $validated['company_folder_id'],
                'therapeutic_part_time' => $request->input('therapeutic_part_time', null),
            ]);
            if ($customAbsence) {
                return response()->json(['message' => 'Absence personnalisée créée', "data" => $customAbsence], 201);
            }
        } else {
            return response()->json(['message' => 'Le code rubrique doit commencer par AB-'], 400);
        }

        return response()->json(['message' => 'Impossible de créer la rubrique personnalisée'], 400);
    }

    public function updateCustomAbsence(Request $request, $id)
    {
        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
            'base_calcul' => 'required|string|max:255',
            'company_folder_id' => 'required|integer',
            'therapeutic_part_time' => 'nullable|boolean',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        // Vérifie si la custom absence avec ce code et ce label existe déjà
        $isCustomAbsenceExists = CustomAbsence::where('company_folder_id', $validated['company_folder_id'])
            ->where('code', $validated['code'])
            ->where('label', $validated['label'])
            ->where('base_calcul', $validated['base_calcul'])
            ->exists();


        $isAbsenceExists = Absence::where('code', $validated['code'])
            ->where('base_calcul', $validated['base_calcul'])
            ->exists();

        if ($isCustomAbsenceExists || $isAbsenceExists) {
            return response()->json(['message' => 'Absence déjà existante.'], 400);
        }

        if (str_starts_with($validated['code'], 'AB-')) {
            $customAbsence = CustomAbsence::where('id', $id)->update([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'base_calcul' => $validated['base_calcul'],
                'therapeutic_part_time' => $request->input('therapeutic_part_time', null),
            ]);
            if ($customAbsence) {
                return response()->json(['message' => 'Absence personnalisée modifiée', "id" => $id], 201);
            }
        } else {
            return response()->json(['message' => 'Le code rubrique doit commencer par AB-'], 400);
        }

        return response()->json(['message' => 'Impossible de modifier la rubrique personnalisée'], 400);
    }

    public function deleteCustomAbsence($id)
    {
        // permet de supprimer dans la mapping la custom hour supprimée
        $companyFolder = CustomAbsence::where('id', $id)->first();
        $companyFolderId = $companyFolder->company_folder_id;
        $nameRubrique = "Absence personnalisée";

        $deletMapping = new Request([
            "companyFolderId" => $companyFolderId,
            "output_rubrique_id" => $id,
            "nameRubrique" => $nameRubrique,
            "input_rubrique" => ""
        ]);

        $controller = new MappingController();
        $controller->deleteOneLineMappingData($deletMapping);

        // supprime l'absence custom
        $deleteCustomAbsence = CustomAbsence::find($id)->delete();
        if ($deleteCustomAbsence) {
            return response()->json(['message' => 'l\'absence custom a été supprimé'], 200);
        } else {
            return response()->json(['message' => 'L\'absence custom n\'existe pas.'], 404);
        }
    }
}
