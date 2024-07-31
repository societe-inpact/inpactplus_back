<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VariablesElements\VariableElement;
use App\Rules\CustomRubricRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VariablesElementsController extends Controller
{

    /**
     * Récupère tous les éléments variables dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la récupération.
     */
    public function getVariablesElements(){
        $variablesElements = VariableElement::all();
        return response()->json($variablesElements, 200);
    }

    /**
     * Crée un nouvel élément variable dans la base de données.
     *
     * Cette fonction valide les données fournies, nettoie le champ 'code' en supprimant
     * les espaces autour du tiret "-", vérifie l'existence de l'élément variable
     * de l'absence générique avec le même code et le même label, puis crée l'absence personnalisée
     * si aucune duplication n'est trouvée.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la création.
     */
    public function createVariableElement(Request $request){

        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
            'company_folder_id' => 'required|integer',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        // Vérification si l'élément variable avec ce code et ce label existe déjà
        $isVariableElementExist = VariableElement::where('company_folder_id', $validated['company_folder_id'])
            ->where('code', $validated['code'])
            ->where('label', $validated['label'])
            ->exists();

        if ($isVariableElementExist) {
            return response()->json(['message' => 'Élément variable déjà existant.'], 400);
        }


        // Création de l'élément variable si le code rubrique commence par EV-
        if (str_starts_with($validated['code'], 'EV-')){
            $variableElement = VariableElement::create([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'company_folder_id' => $validated['company_folder_id'],
            ]);
            if ($variableElement) {
                return response()->json(['message' => 'Élément variable créée', "id" => $variableElement->id], 201);
            }
        }else{
            return response()->json(['message' => 'Le code rubrique doit commencer par EV-'], 400);
        }

        return response()->json(['message' => 'Impossible de créer la rubrique personnalisée'], 400);
    }

    public function updateVariableElement(Request $request,$id)
    {
        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
            'company_folder_id' => 'required|integer',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        // Vérification si l'élément variable avec ce code et ce label existe déjà
        $isVariableElementExist = VariableElement::where('company_folder_id', $validated['company_folder_id'])
            ->where('code', $validated['code'])
            ->where('label', $validated['label'])
            ->exists();

        if ($isVariableElementExist) {
            return response()->json(['message' => 'Élément variable déjà existant.'], 400);
        }


        // Création de l'élément variable si le code rubrique commence par EV-
        if (str_starts_with($validated['code'], 'EV-')){

            $variableElement = VariableElement::where('id',$id)->update([
                'label' => $validated['label'],
                'code' => $validated['code'],
            ]);
            if ($variableElement) {
                return response()->json(['message' => 'Élément variable a été modifié', "id" => $id], 201);
            }
        }else{
            return response()->json(['message' => 'Le code rubrique doit commencer par EV-'], 400);
        }

        return response()->json(['message' => 'Impossible de modifier la rubrique personnalisée'], 400);
    }

    public function deleteVariableElement($id)
    {
        $deleteVariableElement = VariableElement::find($id)->delete();
        if ($deleteVariableElement){
            return response()->json(['message' => 'l\'élément variable a été supprimé'], 200);
        }
        else{
            return response()->json(['message' => 'L\'élément variable n\'existe pas.'], 404);
        }
    }

}
