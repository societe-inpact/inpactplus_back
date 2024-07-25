<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\CustomAbsence;
use App\Models\Mapping;
use Illuminate\Http\Request;


class AbsenceController extends Controller
{
    public function getAbsences(){
        $absences = Absence::all();
        return response()->json($absences, 200);
    }

    public function getCustomAbsences(){
        $customAbsences = CustomAbsence::all();
        return response()->json($customAbsences, 200);
    }

    public function createCustomAbsence(Request $request){
        // Validation des données
        $validated = request()->validate([
            'label' => 'required',
            'code' => 'required',
            'base_calcul' => 'required',
            'company_folder_id' => 'required',
            'therapeutic_part_time' => 'nullable'
        ]);

        if(!$validated){
            return response()->json(['message' => 'Données invalides.'], 400);
        }

        // verifie si la custom absence avec ce code et ce label existe déjà
        $isCustomAbsenceExists = CustomAbsence::all()->where('company_folder_id', request('company_folder_id'))->where('code', $request->get('code'))->where('label', $request->get('label'))->where('base_calcul', $request->get('base_calcul'));
        $isAbsenceExists = Absence::all()->where('code', $request->get('code'))->where('base_calcul', $request->get('base_calcul'));

        if($isCustomAbsenceExists->isNotEmpty() || $isAbsenceExists->isNotEmpty()){
            return response()->json(['message' => 'Absence déjà existante.'], 400);
        }

        $customAbsence = new CustomAbsence();
        $customAbsence->label = request('label');
        $customAbsence->code = request('code');
        $customAbsence->base_calcul = request('base_calcul');
        $customAbsence->company_folder_id = request('company_folder_id');
        $customAbsence->therapeutic_part_time = request('therapeutic_part_time');
        if (str_starts_with($request->get('code'), 'AB-')){
            $customAbsence->save();
            return response()->json($customAbsence, 201);
        }else{
            return response()->json(['message' => 'Le code rubrique doit commencer par AB-'], 400);
        }
    }
}
