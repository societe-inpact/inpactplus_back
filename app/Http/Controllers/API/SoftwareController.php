<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Misc\Software;
use App\Models\Misc\InterfaceSoftware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SoftwareController extends Controller
{
    public function getSoftware(){
        $softwares = Software::all();
        return response()->json($softwares, 200);
    }

    public function updateNameSoftware(Request $request,$id){

        $validator = Validator::make($request->all(), [
            'newNameInterface' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $idInterface = $id;
        $newNameInterface = $request->newNameInterface;
        // dd($oldNameInterface);
        $softwareoldname = Software::all()->where('id',$idInterface)->first();
        if ($softwareoldname){
            $softwareuptade = $softwareoldname->update(['name'=>$newNameInterface]);
            return response()->json(['message' => 'Le nom a été changé en '.$newNameInterface], 200);
        }
        else{
            return response()->json(['error' => 'Lors du changement de nom'], 500);
        }
    }

    public function deleteNameSoftware($id){

        $IdInterface = $id;

        $softwarename = Software::all()->where('id',$IdInterface)->first();
        if ($softwarename){

            $idInterfaceSoftware = $softwarename->interface_software_id;
            if ($idInterfaceSoftware){
                $delinterfaceSoftware = InterfaceSoftware::find($idInterfaceSoftware)->delete();
            }
            $delSoftware = Software::find($IdInterface)->delete();
            return response()->json(['message' => 'l\'interface a été supprimé'], 200);
        }
        else{
            return response()->json(['error' => 'L\interface n\'a pas été trouvé.'], 500);
        }
    }
}
