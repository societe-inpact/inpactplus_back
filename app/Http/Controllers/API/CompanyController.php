<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\Company;
use App\Services\CheckBeforeDeleteService;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    use JSONResponseTrait;

    protected CheckBeforeDeleteService $checkBeforeDeleteService;

    public function __construct(CheckBeforeDeleteService $checkBeforeDeleteService)
    {
        $this->checkBeforeDeleteService = $checkBeforeDeleteService;
    }

    public function getCompanies(){
        $companies = Company::with([
            'folders',
            'folders.referent',
            'modules',
            'referent',
            'employees'
        ])->get();

        if ($companies){
            return $this->successResponse($companies, '');
        }
        return $this->errorResponse('Une erreur est survenue lors de la récupération de la liste des entreprises', 500);
    }

    public function createCompany(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'telephone' => 'nullable|string',
            'referent_id' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'telephone' => $request->telephone,
                'referent_id' => $request->referent_id,
            ];

            $company = Company::create($data);
            if ($company){
                return $this->successResponse($company, 'Entreprise créée avec succès',201);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Une erreur est survenue lors de la création de l\'entreprise', 500);
        }
    }

    public function deleteCompany(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'confirm' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La confirmation est requise');
        }

        // Vérifie les dépendances avant la suppression
        $checkResult = $this->checkBeforeDeleteService->checkCompanyBeforeDelete($id);

        if (isset($checkResult['error'])) {
            return response()->json($checkResult, 404);
        }

        if ($checkResult['confirm'] && !$request->input('confirm')) {
            return response()->json([
                'message' => $checkResult['message'],
                'confirm' => false
            ], 200);
        }

        return $this->confirmDeleteCompany($id);
    }

    protected function confirmDeleteCompany(int $id)
    {
        DB::beginTransaction();

        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'error' => 'Entreprise non trouvée.',
                ], 404);
            }

            // Supprime l'entreprise
            $company->delete();

            DB::commit();

            return response()->json([
                'success' => 'Entreprise supprimée avec succès.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Une erreur est survenue lors de la suppression.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
