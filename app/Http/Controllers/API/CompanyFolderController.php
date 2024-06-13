<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CompanyFolder;
use App\Models\InterfaceFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyFolderController extends Controller
{
    public function createCompanyFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'folder_number' => 'required|integer',
            'folder_name' => 'required|string',
            'siret' => 'required|string',
            'siren' => 'required|string',
            'interface_id' => 'required|exists:interfaces,id',
            'company_id' => 'exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = [
                'folder_number' => $request->folder_number,
                'folder_name' => $request->folder_name,
                'siret' => $request->siret,
                'siren' => $request->siren,
                'interface_id' => $request->interface_id,
                'company_id' => $request->company_id,
            ];
            CompanyFolder::create($data);

            $folder = CompanyFolder::where('folder_number', $request->folder_number)->first();

            if ($folder && $request->interface_id) {
                $interface = InterfaceFolder::create([
                    'company_folder_id' => $folder->id,
                    'interface_folder_id' => $request->interface_id
                ]);                  
            }
            return response()->json(['message' => 'Dossier créé avec succès'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la création du dossier.'], 500);
        }
    }

    public function updateCompanyFolder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'folder_number' => 'required|integer',
            'folder_name' => 'required|string',
            'siret' => 'required|string',
            'siren' => 'required|string',
            'interface_id' => 'required|exists:interfaces,id',
            'company_id' => 'exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = [
                'folder_number' => $request->folder_number,
                'folder_name' => $request->folder_name,
                'siret' => $request->siret,
                'siren' => $request->siren,
                'interface_id' => $request->interface_id,
                'company_id' => $request->company_id,
            ];
            CompanyFolder::where('id', $id)->update($data);
            return response()->json(['message' => 'Dossier modifié avec succès'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la modification du dossier.'], 500);
        }
    }
}
