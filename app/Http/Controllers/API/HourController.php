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
}
