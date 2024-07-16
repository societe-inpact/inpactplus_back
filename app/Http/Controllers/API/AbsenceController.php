<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\CustomAbsence;
use App\Models\Mapping;
use App\Rules\CustomRubricRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class AbsenceController extends Controller
{

    /**
     * Récupère toutes les absences génériques dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la récupération.
     */
    public function getAbsences(){
        $absences = Absence::all();
        return response()->json($absences, 200);
    }


    /**
     * Récupère toutes les absences personnalisées dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la récupération.
     */
    public function getCustomAbsences(){
        $customAbsences = CustomAbsence::all();
        return response()->json($customAbsences, 200);
    }

    /**
     * Crée une nouvelle absence personnalisée dans la base de données.
     *
     * Cette fonction valide les données fournies, nettoie le champ 'code' en supprimant
     * les espaces autour du tiret "-", vérifie l'existence de l'absence personnalisée ou
     * de l'absence générique avec le même code et le même label, puis crée l'absence personnalisée
     * si aucune duplication n'est trouvée.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la création.
     */
    public function createCustomAbsence(Request $request){

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
                return response()->json(['message' => 'Absence personnalisée créée'], 201);
            }
        }else{
            return response()->json(['message' => 'Le code rubrique doit commencer par AB-'], 400);
        }

        return response()->json(['message' => 'Impossible de créer la rubrique personnalisée'], 400);
    }
}
