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
        ]);

        if(!$validated){
            return response()->json(['message' => 'Données invalides.'], 400);
        }

        $isVariableElementExist = VariableElement::where('code', request('code'))->where('label', request('label'))->first();
        if($isVariableElementExist){
            return response()->json(['message' => 'Element variable déjà existant.'], 400);
        }

        $variableElement = new VariableElement();
        $variableElement->label = request('label');
        $variableElement->code = request('code');
        $variableElement->save();
        return response()->json($variableElement, 200);
    }
}
