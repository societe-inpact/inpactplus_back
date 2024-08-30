<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hours\CustomHour;
use App\Models\Hours\Hour;
use App\Rules\CustomRubricRule;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomHourController extends Controller
{
    use JSONResponseTrait;
    /**
     * Récupère toutes les heures personnalisées dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la récupération.
     */
    public function getCustomHours(){
        $customHours = CustomHour::all();
        return $this->successResponse($customHours);
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
    public function createCustomHour(Request $request){

        // Validation des données
        $validated = $request->validate([
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

        if($isHourExists){
            return $this->errorResponse('Heure déjà existante', 403);
        }elseif($isCustomHourExists){
            return $this->errorResponse('Heure personnalisée déjà existante', 403);
        }

        if (str_starts_with($validated['code'], 'HS-')) {
            // Création de l'heure personnalisée
            $customHour = CustomHour::create([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'company_folder_id' => $validated['company_folder_id'],
            ]);
            if ($customHour) {
                return $this->successResponse($customHour, 'Heure personnalisée créée avec succès', 201);
            }
        }else{
            return $this->errorResponse('Le code rubrique doit commencer par HS-');
        }
        return $this->errorResponse('Impossible de créer la rubrique personnalisée', 500);
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
            return $this->errorResponse('Heure personnalisée déjà existante', 403);
        }

        if (str_starts_with($validated['code'], 'HS-')) {
            // Création de l'heure personnalisée
            $customHour = CustomHour::where('id',$id)->update([
                'label' => $validated['label'],
                'code' => $validated['code'],
            ]);
            if ($customHour) {
                return $this->successResponse('', 'Heure personnalisée mise à jour avec succès');
            }
        }else{
            return $this->errorResponse('Le code rubrique doit commencer par HS-');
        }
        return $this->errorResponse('Impossible de modifier la rubrique personnalisée', 500);
    }

    public function deleteCustomHour($id)
    {
        // permet de supprimer dans la mapping la custom hour supprimée
        $companyFolder = CustomHour::where('id', $id)->first();
        $companyFolderId = $companyFolder->company_folder_id;
        $nameRubrique = "Heure personnalisée";

        $deletMapping =new Request([
            "companyFolderId" => $companyFolderId,
            "output_rubrique_id" => $id,
            "nameRubrique" => $nameRubrique,
            "input_rubrique" => ""
        ]);

        $controller = new MappingController();
        $controller->deleteOneLineMappingData($deletMapping);

        // supprime la customhour
        $deleteCustomHour = CustomHour::find($id)->delete();
        if ($deleteCustomHour){
            return $this->successResponse('', 'L\'heure personnalisée a été supprimé avec succès');
        }
        else{
            return $this->errorResponse('L\'heure personnalisée n\'existe pas', 404);
        }
    }
}
