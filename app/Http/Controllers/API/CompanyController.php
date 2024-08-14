<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function getCompanies(){
        $companies = Company::with([
            'folders',
            'folders.referent',
            'modules',
            'referent'
        ])->get();

        $companies->each(function ($company) {
            $company->employees = DB::table('users')
                ->join('employee_folder', 'users.id', '=', 'employee_folder.user_id')
                ->join('company_folders', 'employee_folder.company_folder_id', '=', 'company_folders.id')
                ->where('company_folders.company_id', $company->id)
                ->select('users.*')
                ->get();
        });

        return response()->json($companies);
    }

    public function createCompany(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'referent_id' => 'exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'referent_id' => $request->referent_id,
            ];

            $company = Company::create($data);
            return response()->json(['message' => 'Entreprise créée avec succès', 'company' => ['id' => $company->id]], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la création de l\'entreprise.'], 500);
        }
    }
}
