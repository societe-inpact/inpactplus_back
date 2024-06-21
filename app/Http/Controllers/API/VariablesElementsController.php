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

    public function createVariableElement(){
        $validated = request()->validate([
            'label' => 'required',
            'code' => 'required',
            'company_folder_id' => 'required',
        ]);

        if(!$validated){
            return response()->json(['message' => 'Données invalides.'], 400);
        }

        $isVariableElementExist = VariableElement::all()->where('company_folder_id', request('company_folder_id'))->where('code', request('code'))->where('label', request('label'));

        if($isVariableElementExist->isNotEmpty()){
            return response()->json(['message' => 'Element variable déjà existant.'], 400);
        }

        $variableElement = new VariableElement();
        $variableElement->label = request('label');
        $variableElement->code = request('code');
        $variableElement->company_folder_id = request('company_folder_id');
        $variableElement->save();
        return response()->json($variableElement, 200);
    }
}
