<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyEntityController extends Controller
{
    public function getCompanyEntity(){
        $company = Company::with('company_entity');
    }
}
