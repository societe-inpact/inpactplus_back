<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VariablesElements\VariableElement;
use App\Rules\CustomRubricRule;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VariablesElementsController extends Controller
{
    use JSONResponseTrait;

    /**
     * Récupère tous les éléments variables dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la récupération.
     */
    public function getVariablesElements()
    {
        $variablesElements = VariableElement::all();
        return $this->successResponse($variablesElements);
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
    public function createVariableElement(Request $request)
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
            return $this->errorResponse('Élément variable déjà existant', 403);
        }


        // Création de l'élément variable si le code rubrique commence par EV-
        if (str_starts_with($validated['code'], 'EV-')) {
            $variableElement = VariableElement::create([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'company_folder_id' => $validated['company_folder_id'],
            ]);
            if ($variableElement) {
                return $this->successResponse($variableElement, 'Élément variable créé avec succès', 201);
            }
        } else {
            return $this->errorResponse('Le code rubrique doit commencer par EV-');
        }
        return $this->errorResponse('Impossible de créer la rubrique personnalisée', 500);
    }

    public function updateVariableElement(Request $request, $id)
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
            return $this->errorResponse('Élément variable déjà existant', 403);
        }


        // Création de l'élément variable si le code rubrique commence par EV-
        if (str_starts_with($validated['code'], 'EV-')) {

            $variableElement = VariableElement::where('id', $id)->update([
                'label' => $validated['label'],
                'code' => $validated['code'],
            ]);
            if ($variableElement) {
                return $this->successResponse($variableElement, 'L\'élément variable a été mis à jour avec succès');
            }
        } else {
            return $this->errorResponse('Le code rubrique doit commencer par EV-');
        }
        return $this->errorResponse('Impossible de modifier la rubrique personnalisée', 500);
    }

    public function deleteVariableElement($id)
    {
        // permet de supprimer dans la mapping la custom hour supprimée
        $companyFolder = VariableElement::where('id', $id)->first();
        $companyFolderId = $companyFolder->company_folder_id;
        $nameRubrique = "Éléments variables";

        $deletMapping = new Request([
            "companyFolderId" => $companyFolderId,
            "output_rubrique_id" => $id,
            "nameRubrique" => $nameRubrique,
            "input_rubrique" => ""
        ]);

        $controller = new MappingController();
        $controller->deleteOneLineMappingData($deletMapping);

        // supprime l'élément variable

        $deleteVariableElement = VariableElement::find($id)->delete();
        if ($deleteVariableElement) {
            return $this->successResponse('', 'L\'élément variable a été supprimé avec succès');
        } else {
            return $this->errorResponse('L\'élément variable n\'existe pas', 404);
        }
    }

}
