<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Misc\InterfaceSoftware;
use App\Models\Misc\InterfaceMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InterfaceController extends Controller
{
    public function getInterfaces(){
        $softwares = InterfaceSoftware::all();
        return response()->json($softwares, 200);
    }

    // Où est la création ? Crud incomplet ?

    public function updateNameInterface(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $software = InterfaceSoftware::findOrFail($id);

        if ($software->update()){
            return response()->json(['message' => 'Le nom a été changé en '. $request->name], 200);
        } else{
            return response()->json(['error' => 'Erreur lors du changement de nom'], 500);
        }
    }

    public function deleteNameInterface($id){
        $software = InterfaceSoftware::findOrFail($id);

        if ($software->delete()){
            return response()->json(['message' => 'L\'interface a été supprimée'], 200);
        }
        else{
            return response()->json(['error' => 'L\'interface n\'existe pas'], 500);
        }
    }
}
