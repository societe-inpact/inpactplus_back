<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\CompanyFolder;
use App\Models\Mapping\Mapping;
use App\Models\Misc\CompanyFolderInterface;
use App\Models\Misc\InterfaceSoftware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyFolderController extends Controller
{
    public function getCompanyFolders(){

        $this->authorize('read_company_folder', CompanyFolder::class);

        $companyFolders = CompanyFolder::with('modules','company','interfaces','mappings','employees', 'referent')->get();
        return response()->json($companyFolders);
    }
    public function createCompanyFolder(Request $request)
    {
        $this->authorize('create_company_folder', CompanyFolder::class);

        $validator = Validator::make($request->all(), [
            'folder_number' => 'required|string',
            'folder_name' => 'required|string',
            'siret' => 'required|string',
            'siren' => 'required|string',
            'telephone' => 'nullable|string',
            'company_id' => 'exists:companies,id',
            'interface_id' => 'exists:interfaces,id',
            'notes' => 'nullable|string',
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
                'telephone' => $request->telephone,
                'company_id' => $request->company_id,
                'interface_id' => $request->interface_id,
                'notes' => $request->notes,
            ];
            $existingFolder = CompanyFolder::all()->where('folder_number', '==', $request->get('folder_number'))->first();
            if ($existingFolder){
                return response()->json(['message' => 'Le numéro de dossier ' . $request->folder_number . ' est déjà associé à ' . $existingFolder->folder_name]);
            }

            CompanyFolder::create($data);

            $folder = CompanyFolder::where('folder_number', $request->folder_number)->first();

            CompanyFolderInterface::create([
                'company_folder_id' => $folder->id,
                'interface_id' => $request->interface_id
            ]);
            Mapping::create([
                'company_folder_id' => $folder->id,
                'data' => [],
            ]);
            return response()->json(['message' => 'Dossier créé avec succès'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la création du dossier.'], 500);
        }
    }

    public function updateCompanyFolder(Request $request, $id)
    {
        $this->authorize('update_company_folder', CompanyFolder::class);

        $validator = Validator::make($request->all(), [
            'folder_number' => 'required|string',
            'folder_name' => 'required|string',
            'siret' => 'required|string',
            'siren' => 'required|string',
            'referent' => 'nullable|exists:users,id',
            'company_id' => 'nullable|exists:companies,id',
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
                'referent_id' => $request->referent_id,
                'company_id' => $request->company_id,
            ];

            if ($request->has('referent_id')) {
                $data['referent_id'] = $request->referent_id;
            }

            if ($request->has('company_id')) {
                $data['company_id'] = $request->company_id;
            }

            CompanyFolder::where('id', $id)->update($data);
            return response()->json(['message' => 'Dossier modifié avec succès'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la modification du dossier.'], 500);
        }
    }

    public function addInterfaceToCompanyFolder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'interface_id' => 'required|exists:interfaces,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = [
                'company_folder_id' => $id,
                'interface_id' => $request->interface_id,
            ];
            $existingFolderInterface = CompanyFolderInterface::all()->where('interface_id', '==', $request->get('interface_id'))->first();
            if ($existingFolderInterface){
                $existingInterface = InterfaceSoftware::findOrFail($request->interface_id);
                return response()->json(['message' => 'L\'interface ' . $existingInterface->name . ' est déjà associée au dossier']);
            }
            CompanyFolderInterface::create($data);
            return response()->json(['message' => 'Interface ajoutée avec succès'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de l\'ajout de l\'interface'], 500);
        }
    }

    public function deleteInterfaceFromCompanyFolder($companyFolderId, $interfaceId)
    {
        try {
            // Trouver l'association actuelle entre le 'company_folder' et l'interface
            $companyFolderInterface = CompanyFolderInterface::where('company_folder_id', $companyFolderId)
                ->where('interface_id', $interfaceId)
                ->first();

            if (!$companyFolderInterface) {
                return response()->json(['message' => 'Aucune association trouvée pour cette interface et ce dossier'], 404);
            }

            // Supprimer l'association entre le dossier et l'interface
            $companyFolderInterface->delete();

            return response()->json(['message' => 'Interface supprimée avec succès'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la suppression de l\'interface'], 500);
        }
    }
}
