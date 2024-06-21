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

    public function createCustomHour(Request $request){
        $validated = request()->validate([
            'label' => 'required',
            'code' => 'required',
            'company_folder_id' => 'required',
        ]);

        if(!$validated){
            return response()->json(['message' => 'Données invalides.'], 400);
        }
        // verifie si la custom absence avec ce code et ce label existe déjà
        $isCustomHourExists = CustomHour::all()->where('company_folder_id', request('company_folder_id'))->where('code', $request->get('code'))->where('label', $request->get('label'));
        $isHourExists = Hour::all()->where('code', $request->get('code'));

        if($isCustomHourExists->isNotEmpty() || $isHourExists->isNotEmpty()){
            return response()->json(['message' => 'Heure déjà existante.'], 400);
        }

        $customHour = new CustomHour();
        $customHour->label = request('label');
        $customHour->code = request('code');
        $customHour->company_folder_id = request('company_folder_id');
        $customHour->save();
        return response()->json($customHour, 200);
    }
}
