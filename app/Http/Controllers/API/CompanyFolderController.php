<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Mapping\Mapping;
use App\Models\Misc\CompanyFolderInterface;
use App\Models\Misc\InterfaceSoftware;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyFolderController extends Controller
{

    use JSONResponseTrait;

    public function getCompanyFolders(){

        $this->authorize('read_company_folder', CompanyFolder::class);

        $companyFolders = CompanyFolder::with('modules','company','interfaces','mappings','employees', 'referent')->get();
        if ($companyFolders){
            return $this->successResponse($companyFolders, '');
        }
        return $this->errorResponse('Une erreur est survenue lors de la récupération de la liste des dossiers d\'entreprise', 500);
    }

    public function getCompanyFolder($id){
        $this->authorize('read_company_folder', CompanyFolder::class);

        $companyFolder = CompanyFolder::with('modules','company','interfaces','mappings','employees', 'referent')->findOrFail($id);
        if ($companyFolder){
            return $this->successResponse($companyFolder, '');
        }
        return $this->errorResponse('Une erreur est survenue lors de la récupération du dossier de l\'entreprise', 500);
    }

    public function createCompanyFolder(Request $request){

        $this->authorize('create_company_folder', CompanyFolder::class);

        if (!$request->referent_id){
            $request->merge(['referent_id' => Company::findOrFail($request->company_id)->referent_id]);
        }

        $validator = Validator::make($request->all(), [
            'folder_number' => 'required|string',
            'folder_name' => 'required|string',
            'siret' => 'required|string',
            'siren' => 'required|string',
            'telephone' => 'nullable|string',
            'company_id' => 'exists:companies,id',
            'interface_id' => 'exists:interfaces,id',
            'referent_id' => 'exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
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
                'referent_id' => $request->referent_id,
                'notes' => $request->notes,
            ];

            $existingFolder = CompanyFolder::all()->where('folder_number', '==', $request->get('folder_number'))->first();
            if ($existingFolder){
                return $this->errorResponse('Le numéro de dossier ' . $request->folder_number . ' est déjà associé au dossier ' . $existingFolder->folder_name, 409);
            }

            $companyFolder = CompanyFolder::create($data);
            if ($companyFolder){
                $companyFolder = CompanyFolder::where('folder_number', $request->folder_number)->first();

                if ($companyFolder){
                    CompanyFolderInterface::create([
                        'company_folder_id' => $companyFolder->id,
                        'interface_id' => $request->interface_id
                    ]);
                    Mapping::create([
                        'company_folder_id' => $companyFolder->id,
                        'data' => [],
                    ]);
                    return $this->successResponse('', 'Dossier créé avec succès', 201);
                }
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Une erreur est survenue lors de la création du dossier',500);
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
            return $this->errorResponse($validator->errors(), 422);
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

            $companyFolderUpdate = CompanyFolder::where('id', $id)->update($data);
            if ($companyFolderUpdate){
                return $this->successResponse('', 'Dossier mis à jour avec succès', 201);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du dossier', 500);
        }
    }

    public function addInterfaceToCompanyFolder(Request $request, $id)
    {
        $this->authorize('create_interface_company_folder', InterfaceSoftware::class);

        $validator = Validator::make($request->all(), [
            'interface_id' => 'required|exists:interfaces,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            $data = [
                'company_folder_id' => $id,
                'interface_id' => $request->interface_id,
            ];

            $existingFolderInterface = CompanyFolderInterface::where('interface_id', $request->get('interface_id'))
                ->where('company_folder_id', $id)
                ->first();
            if ($existingFolderInterface){
                $existingInterface = InterfaceSoftware::findOrFail($request->interface_id);
                return $this->errorResponse('L\'interface ' . $existingInterface->name . ' est déjà associée au dossier', 409);
            }

            $createCompanyFolderInterface = CompanyFolderInterface::create($data);
            if ($createCompanyFolderInterface){
                return $this->successResponse('', 'Interface ajoutée au dossier avec succès', 201);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Une erreur est survenue lors de l\'ajout de l\'interface', 500);
        }
    }

    public function deleteInterfaceFromCompanyFolder($companyFolderId, $interfaceId)
    {
        $this->authorize('create_interface_company_folder', InterfaceSoftware::class);

        try {
            // Trouve l'association actuelle entre le 'company_folder' et l'interface
            $companyFolderInterface = CompanyFolderInterface::where('company_folder_id', $companyFolderId)
                ->where('interface_id', $interfaceId)
                ->first();

            if ($companyFolderInterface){
                // Supprime l'association entre le dossier et l'interface
                $deleteCompanyFolderInterface = $companyFolderInterface->delete();
                if ($deleteCompanyFolderInterface){
                    return $this->successResponse('', 'Interface supprimée du dossier avec succès', 201);
                }
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Une erreur est survenue lors de la suppression de l\'interface', 500);
        }
    }
}
