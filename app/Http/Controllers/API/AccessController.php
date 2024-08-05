<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employees\EmployeeFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccessController extends Controller
{
    public function addUserToCompanyFolder(Request $request) {
        $validator = Validator::make($request->all(), [
            'is_referent' => 'required|boolean',
            'has_access' => 'required|boolean',
            'user_id' => 'required|exists:users,id',
            'company_folder_id' => 'required|exists:company_folders,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            $employeeFolder = EmployeeFolder::create([
                'is_referent' => $request->is_referent,
                'has_access' => $request->has_access,
                'user_id' => $request->user_id,
                'company_folder_id' => $request->company_folder_id
            ]);
    
            return response()->json([
                'message' => 'Utilisateur ajouté au dossier avec succès',
                'status' => 200,
                'data' => $employeeFolder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'ajout de l\'utilisateur au dossier',
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteUserFromCompanyFolder(Request $request){
        $request->validate([
            'user_id' => 'required|integer',
        ]);
        $userToDelete = EmployeeFolder::where('user_id', intval($request->user_id))->delete();
        if ($userToDelete){
            return response()->json(['message' => 'Utilisateur supprimé du dossier avec succès']);
        }
        return response()->json(['message' => 'Erreur lors de la suppression de l\'utilisateur']);
    }
}
