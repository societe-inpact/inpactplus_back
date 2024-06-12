<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Software;
use Illuminate\Http\Request;

class SoftwareController extends Controller
{
    public function getSoftware()
    {
        $softwares = Software::all();
        return response()->json($softwares, 200);
    }
}
