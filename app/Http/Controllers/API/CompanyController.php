<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\Company;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    use JSONResponseTrait;

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
}
