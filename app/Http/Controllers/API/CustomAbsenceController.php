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
        return $this->successResponse($customAbsences);
    }

    public function createCustomAbsence(Request $request)
    {

        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
            'base_calcul' => 'required|string|max:255',
            'company_folder_id' => 'required|integer',
            'therapeutic_part_time' => 'nullable|numeric',
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
            return $this->errorResponse('Absence déjà existante', 403);
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
                return $this->successResponse($customAbsence, 'Absence personnalisée créée avec succès', 201);
            }
        } else {
            return $this->errorResponse('Le code rubrique doit commencer par AB-');
        }
        return $this->errorResponse('Impossible de créer la rubrique personnalisée', 500);
    }

    public function updateCustomAbsence(Request $request, $id)
    {
        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
            'base_calcul' => 'required|string|max:255',
            'company_folder_id' => 'required|integer',
            'therapeutic_part_time' => 'nullable|numeric',
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
            return $this->errorResponse('Absence déjà existante', 403);
        }

        if (str_starts_with($validated['code'], 'AB-')) {
            $customAbsence = CustomAbsence::where('id', $id)->update([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'base_calcul' => $validated['base_calcul'],
                'therapeutic_part_time' => $request->input('therapeutic_part_time', null),
            ]);
            if ($customAbsence) {
                return $this->successResponse('', 'L\'absence personnalisée a été mis à jour avec succès');
            }
        } else {
            return $this->errorResponse('Le code rubrique doit commencer par AB-');
        }
        return $this->errorResponse('Impossible de modifier la rubrique personnalisée', 500);
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
        // TODO : Delete le mapping lié à la custom absence
        dd($deletMapping);

        // supprime l'absence custom
        $deleteCustomAbsence = CustomAbsence::find($id)->delete();
        if ($deleteCustomAbsence) {
            return $this->successResponse('', 'L\'absence personnalisée a été supprimé avec succès');
        } else {
            return $this->errorResponse('L\'absence personnalisée n\'existe pas', 404);
        }
    }
}
