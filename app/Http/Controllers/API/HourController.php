<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hours\CustomHour;
use App\Models\Hours\Hour;
use App\Rules\CustomRubricRule;
use Illuminate\Http\JsonResponse;

class HourController extends Controller
{

    /**
     * Récupère toutes les heures génériques dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la récupération.
     */
    public function getHours(){
        $hours = Hour::all();
        return response()->json($hours);
    }

    /**
     * Récupère toutes les heures personnalisées dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la récupération.
     */
    public function getCustomHours(){
        $customHours = CustomHour::all();
        return response()->json($customHours);
    }

    /**
     * Crée une nouvelle heure personnalisée dans la base de données.
     *
     * Cette fonction valide les données fournies, nettoie le champ 'code' en supprimant
     * les espaces autour du tiret "-", vérifie l'existence de l'heure personnalisée ou
     * de l'heure générique avec le même code, puis crée l'heure personnalisée si aucune
     * duplication n'est trouvée.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la création.
     */
    public function createCustomHour(){

        // Validation des données
        $validated = request()->validate([
            'label' => 'required',
            'code' => ['required', new CustomRubricRule],
            'company_folder_id' => 'required|integer',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        // Vérification si une heure personnalisée avec ce code et ce label existe déjà
        $isCustomHourExists = CustomHour::where('company_folder_id', $validated['company_folder_id'])
            ->where('code', $validated['code'])
            ->where('label', $validated['label'])
            ->exists();

        // Vérification si une heure avec ce code existe déjà
        $isHourExists = Hour::where('code', $validated['code'])->exists();

        if($isCustomHourExists || $isHourExists){
            return response()->json(['message' => 'Heure personnalisée déjà existante.'], 400);
        }

        if (str_starts_with($validated['code'], 'HS-')) {
            // Création de l'heure personnalisée
            $customHour = CustomHour::create([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'company_folder_id' => $validated['company_folder_id'],
            ]);
            if ($customHour) {
                return response()->json(['message' => 'Heure personnalisée créée'], 201);
            }
        }else{
            return response()->json(['message' => 'Le code rubrique doit commencer par HS-'], 400);
        }

        return response()->json(['message' => 'Impossible de créer la rubrique personnalisée'], 400);
    }
}
