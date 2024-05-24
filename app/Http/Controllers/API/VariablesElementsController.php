<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VariableElement;
use Illuminate\Http\Request;

class VariablesElementsController extends Controller
{
    public function getVariablesElements(){
        $variablesElements = VariableElement::all();
        return response()->json($variablesElements, 200);
    }
}
