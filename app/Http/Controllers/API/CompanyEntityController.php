<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;

class CompanyEntityController extends Controller
{
    public function getCompanyEntity(){
        $company = Company::with('company_entity');
    }
}
