<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\CompanyFolder;
use App\Models\Employees\UserCompanyFolder;
use App\Models\Mapping\Mapping;
use App\Traits\JSONResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccessController extends Controller
{
    use JSONResponseTrait;

    /**
     * @throws AuthorizationException
     */
    public function addUserToCompanyFolder(Request $request, $id)
    {
        $this->authorize('add_user_to_company_folder', UserCompanyFolder::class);

        $userIsExists = UserCompanyFolder::where('user_id', $request->user_id)->exists();
        $companyFolder = CompanyFolder::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'has_access' => 'required|boolean',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        if (!$userIsExists){
            try {
                $employeeFolder = UserCompanyFolder::create([
                    'has_access' => $request->has_access,
                    'user_id' => $request->user_id,
                    'company_folder_id' => $companyFolder->id
                ]);
                return $this->successResponse($employeeFolder, 'Utilisateur ajouté au dossier avec succès');

            } catch (\Exception $e) {
                return $this->errorResponse('Une erreur est survenue lors de l\'ajout de l\'utilisateur au dossier', 500);
            }
        }
        return $this->errorResponse('L\'utilisateur est déjà associé au dossier', 403);

    }

    public function deleteUserFromCompanyFolder(Request $request, $id)
    {
        $this->authorize('delete_user_from_company_folder', UserCompanyFolder::class);
        $companyFolder = CompanyFolder::findOrFail($id);

        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $userToDelete = UserCompanyFolder::where('user_id', $request->user_id)->where('company_folder_id', $companyFolder->id)->delete();
        if ($userToDelete) {
            return $this->successResponse('', 'Utilisateur supprimé du dossier avec succès');
        }
        return $this->errorResponse('Erreur lors de la suppression de l\'utilisateur', 500);
    }
}
