<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employees\UserCompanyFolder;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccessController extends Controller
{
    use JSONResponseTrait;

    public function addUserToCompanyFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_referent' => 'required|boolean',
            'has_access' => 'required|boolean',
            'user_id' => 'required|exists:users,id',
            'company_folder_id' => 'required|exists:company_folders,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            $employeeFolder = UserCompanyFolder::create([
                'is_referent' => $request->is_referent,
                'has_access' => $request->has_access,
                'user_id' => $request->user_id,
                'company_folder_id' => $request->company_folder_id
            ]);
            return $this->successResponse($employeeFolder, 'Utilisateur ajouté au dossier avec succès');

        } catch (\Exception $e) {
            return $this->errorResponse('Une erreur est survenue lors de l\'ajout de l\'utilisateur au dossier', 500);
        }
    }

    public function deleteUserFromCompanyFolder(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);
        $userToDelete = UserCompanyFolder::where('user_id', intval($request->user_id))->delete();
        if ($userToDelete) {
            return $this->successResponse('', 'Utilisateur supprimé du dossier avec succès');
        }
        return $this->errorResponse('Erreur lors de la suppression de l\'utilisateur', 500);
    }
}
