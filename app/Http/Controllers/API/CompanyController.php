<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function getCompanies(){
        $companies = Company::with('folders')->get();
        return response()->json($companies);
    }

    public function createCompany(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'referent_id' => 'nullable|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $data = [
                'name' => $request->name,
                'description' => $request->description,
            ];

            if ($request->has('referent_id')) {
                $data['referent_id'] = $request->referent_id;
            }

            Company::create($data);
            return response()->json(['message' => 'Entreprise créée avec succès'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la création de l\'entreprise.'], 500);
        }
    }
}
