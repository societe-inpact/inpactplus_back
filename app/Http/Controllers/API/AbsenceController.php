<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absences\Absence;
use App\Models\Absences\CustomAbsence;
use App\Rules\CustomRubricRule;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class AbsenceController extends Controller
{
    use JSONResponseTrait;

    /**
     * Récupère toutes les absences génériques dans la base de données.
     */
    public function getAbsences()
    {
        $absences = Absence::all();
        return $this->successResponse($absences,'');
    }

    public function createAbsence(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
            'base_calcul' => 'required|string|max:255',
            'therapeutic_part_time' => 'nullable|boolean',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        $isAbsenceExists = Absence::where('code', $validated['code'])->exists();

        if ($isAbsenceExists) {
            return $this->errorResponse('Absence déjà existante', 403);
        }

        if (str_starts_with($validated['code'], 'AB-')) {
            // Création de l'absence personnalisée
            $absence = Absence::create([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'base_calcul' => $validated['base_calcul'],
                'therapeutic_part_time' => $request->input('therapeutic_part_time', null),
            ]);
            if ($absence) {
                return $this->successResponse($absence, 'Absence générique créée', 201);
            }
        } else {
            return $this->errorResponse('Le code rubrique doit commencer par AB-');
        }
        return $this->errorResponse('Impossible de créer la rubrique', 500);
    }

    public function updateAbsence(Request $request, $id)
    {
        $absence = Absence::findOrFail($id);

        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule()],
            'base_calcul' => 'required|string|max:255',
            'therapeutic_part_time' => 'nullable|boolean',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        // Vérification si le code existe déjà pour une autre absence
        $isAbsenceExists = Absence::where('code', $validated['code'])
            ->where('id', '!=', $id)
            ->exists();

        if ($isAbsenceExists) {
            return $this->errorResponse('Absence déjà existante', 403);
        }

        // Mise à jour de l'absence
        if ($absence->update($validated)) {
            return $this->successResponse($absence, 'Absence mise à jour avec succès', 201);
        } else {
            return $this->errorResponse('Erreur lors de la mise à jour de l\'absence', 500);
        }
    }

    public function deleteAbsence($id)
    {
        $absence = Absence::findOrFail($id);
        if ($absence->delete()){
            return $this->successResponse('', 'Absence supprimée avec succès', 201);
        } else {
            return $this->errorResponse('Erreur lors de la suppression de l\'absence', 500);
        }
    }
}
