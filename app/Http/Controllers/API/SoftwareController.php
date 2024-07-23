<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Misc\Software;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SoftwareController extends Controller
{
    public function getSoftware()
    {
        $softwares = Software::all();
        return response()->json($softwares, 200);
    }

    public function createSoftware(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $software = Software::create([
            'name' => $request->name,
        ]);

        if (!$software){
            return response()->json(['message' => 'Erreur lors de la création de l\'interface']);
        }
        return response()->json(['message' => 'Interface créée avec succès']);
    }

    public function updateSoftware(Request $request, $id){
        $software = Software::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $software->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Interface mise à jour avec succès']);
    }

    public function deleteSoftware($id){

        $software = Software::findOrFail($id);
        if ($software){
            Software::destroy($id);
            return response()->json(['message' => 'Interface supprimée avec succès']);
        }else{
            return response()->json(['message' => 'Erreur lors de la mise à jour de l\'interface']);
        }
    }
}
