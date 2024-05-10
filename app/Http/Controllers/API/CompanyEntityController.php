<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;

class CompanyEntityController extends Controller
{
    public function getCompanyEntity(){
        
        $company_entities = Company::with('company_entities')->get();
        return response()->json($company_entities, 200);
    }
}
