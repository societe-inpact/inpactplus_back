<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hours\CustomHour;
use App\Models\Hours\Hour;
use App\Rules\CustomRubricRule;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HourController extends Controller
{
    use JSONResponseTrait;

    /**
     * Récupère toutes les heures génériques dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la récupération.
     */
    public function getHours(){
        $hours = Hour::all();
        return response()->json($hours);
    }

    public function createHour(Request $request){
        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        $isHourExists = Hour::where('code', $validated['code'])->exists();

        if ($isHourExists) {
            return response()->json(['message' => 'Heure déjà existante.'], 400);
        }

        if (str_starts_with($validated['code'], 'HS-')) {
            // Création de l'absence personnalisée
            $absence = Hour::create([
                'label' => $validated['label'],
                'code' => $validated['code'],
            ]);
            if ($absence) {
                return response()->json(['message' => 'Heure générique créée'], 201);
            }
        }else{
            return response()->json(['message' => 'Le code rubrique doit commencer par HS-'], 400);
        }

        return response()->json(['message' => 'Impossible de créer la rubrique'], 400);
    }

    public function updateHour()
    {
        // TODO : Update une heure générique
    }

    public function deleteHour()
    {
        // TODO : Delete une heure générique
    }
}
