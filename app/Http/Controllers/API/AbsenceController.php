<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\CustomAbsence;

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
}
