<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Misc\Software;

class SoftwareController extends Controller
{
    public function getSoftware()
    {
        $softwares = Software::all();
        return response()->json($softwares, 200);
    }
}
