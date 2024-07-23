<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\CompanyFolder;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function createUpdateDeleteNote(Request $request){
        $companyFolderId = $request->get('company_folder_id');
        $companyFolder = CompanyFolder::findOrFail($companyFolderId);
        $validatedData = request()->validate([
            'notes' => 'nullable',
        ]);

        if ($validatedData){
            if (isset($validatedData['notes']) && $companyFolder->notes !== null){
                if ($companyFolder->update(['notes' => $validatedData['notes']])){
                    return response()->json(['message' => 'Note du dossier mise à jour']);
                }
            }
            if ($validatedData['notes'] === null){
                if ($companyFolder->update(['notes' => $validatedData['notes']])){
                    return response()->json(['message' => 'Note du dossier supprimée']);
                }
            }
            if ($companyFolder->notes === null){
                if ($companyFolder->update(['notes' => $validatedData['notes']])){
                    return response()->json(['message' => 'Note du dossier créée']);
                }
            }
        }
        return response()->json(['message' => 'Erreur lors de la création de la note']);
    }
}
