<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function getAbsences(){
        $absences = Absence::all();
        return response()->json($absences, 200);
    }
}
