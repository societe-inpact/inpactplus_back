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
        ]);

        if(!$validated){
            return response()->json(['message' => 'Données invalides.'], 400);
        }

        // verifie si la custom absence avec ce code et ce label existe déjà
        $isCustomAbsenceExists = CustomAbsence::all()->where('code', $request->get('code'));
        if($isCustomAbsenceExists){
            return response()->json(['message' => 'Absence personnalisé déjà existante.'], 400);
        }

        $customAbsence = new CustomAbsence();
        $customAbsence->label = request('label');
        $customAbsence->code = request('code');
        $customAbsence->base_calcul = request('base_calcul');
        $customAbsence->therapeutic_part_time = request('therapeutic_part_time');
        $customAbsence->save();
        return response()->json($customAbsence, 200);
    }
}
