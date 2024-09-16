<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\Company;
use App\Models\Employees\UserCompanyFolder;
use App\Services\CheckBeforeDeleteService;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $this->authorize('read_company', Company::class);

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
        $user = Auth::user();

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
                $date = now()->format('d/m/Y à H:i');
                activity()->performedOn($company)->log($user->firstname . ' ' . $user->lastname . ' a créé l\'entreprise ' . $company->name . ' le ' . $date);
                return $this->successResponse($company, 'Entreprise créée avec succès',201);
            }

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
        return $this->errorResponse('Une erreur est survenue lors de la création de l\'entreprise', 500);

    }

    public function updateCompany(Request $request, $id){
        $user = Auth::user();
        $company = Company::findOrFail($id);

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
            $oldName = $company->name;

            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'telephone' => $request->telephone,
                'referent_id' => $request->referent_id,
            ];

            $companyUpdated = $company->update($data);

            if ($companyUpdated) {
                $newName = $company->name;
                $date = now()->format('d/m/Y à H:i');

                activity()
                    ->performedOn($company)
                    ->log($user->firstname . ' ' . $user->lastname . ' a mis à jour l\'entreprise "' . $oldName . '" à "' . $newName . '" le ' . $date);

                return $this->successResponse('', 'Entreprise mise à jour avec succès');
            }

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
        return $this->errorResponse('Une erreur est survenue lors de la mise à jour de l\'entreprise', 500);
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
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $company = Company::find($id);

            if (!$company) {
                return $this->errorResponse('Entreprise non trouvée', 404);
            }

            $deletedCompany = $company->delete();

            DB::commit();

            if ($deletedCompany){
                $date = now()->format('d/m/Y à H:i');
                activity()->performedOn($company)->log($user->firstname . ' ' . $user->lastname . ' a supprimé l\'entreprise ' . $company->name . ' le ' . $date);

                return $this->successResponse('', 'Entreprise supprimée avec succès');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
        return $this->errorResponse('Une erreur est survenue lors de la suppression', 500);
    }
}
