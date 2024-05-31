<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CustomHour;
use App\Models\Hour;
use Illuminate\Http\Request;

class HourController extends Controller
{
    public function getHours(){
        $hours = Hour::all();
        return response()->json($hours, 200);
    }

    public function getCustomHours(){
        $customHours = CustomHour::all();
        return response()->json($customHours, 200);
    }

    public function createCustomHour(){
        $validated = request()->validate([
            'label' => 'required',
            'code' => 'required',
        ]);

        $isCustomHourExist = CustomHour::where('code', request('code'))->where('label', request('label'))->first();
        if($isCustomHourExist){
            return response()->json(['message' => 'Heure personnalisée déjà existante.'], 400);
        }
        
        $customHour = new CustomHour();
        $customHour->label = request('label');
        $customHour->code = request('code');
        $customHour->save();
        return response()->json($customHour, 200);
    }
}
