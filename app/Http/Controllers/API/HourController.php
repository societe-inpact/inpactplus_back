<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hours\CustomHour;
use App\Models\Hours\Hour;
use App\Rules\CustomRubricRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                return response()->json(['message' => 'Heure personnalisée créée', "id" => $customHour->id], 201);
            }
        }else{
            return response()->json(['message' => 'Le code rubrique doit commencer par HS-'], 400);
        }

        return response()->json(['message' => 'Impossible de créer la rubrique personnalisée'], 400);
    }

    public function updateCustomHour(Request $request, $id)
    {
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
            $customHour = CustomHour::where('id',$id)->update([
                'label' => $validated['label'],
                'code' => $validated['code'],
            ]);
            if ($customHour) {
                return response()->json(['message' => 'Heure personnalisée modifiée', "id" => $id], 201);
            }
        }else{
            return response()->json(['message' => 'Le code rubrique doit commencer par HS-'], 400);
        }

        return response()->json(['message' => 'Impossible de modifier la rubrique personnalisée'], 400);
    }

    public function deleteCustomHour($id)
    {
        // permet de supprimer dans la mapping la custom hour supprimée
        $companyFolder = CustomHour::where('id', $id)->first();
        $companyFolderId = $companyFolder->company_folder_id;
        $nameRubrique = "Heure personnalisée";


        $controller = new MappingController();
        $data =  $controller->deleteOneInfoMappingData($companyFolderId,$id,$nameRubrique);

        // supprime la customhour
        $deleteCustomHour = CustomHour::find($id)->delete();
        if ($deleteCustomHour){
            return response()->json(['message' => 'l\'heure custom a été supprimé'], 200);
        }
        else{
            return response()->json(['message' => 'L\'heure custom n\'existe pas.'], 404);
        }
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
