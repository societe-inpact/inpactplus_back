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

    public function updateNameSoftware(Request $request){

        $validator = Validator::make($request->all(), [
            'oldNameInterface' => 'required|string',
            'newNameInterface' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldNameInterface = $request->oldNameInterface;
        $newNameInterface = $request->newNameInterface;
        // dd($oldNameInterface);
        $softwareoldname = Software::all()->where('name',$oldNameInterface)->first();
        if ($softwareoldname){
            $softwareuptade = $softwareoldname->update(['name'=>$newNameInterface]);
            return response()->json(['message' => 'Le nom a été changé en '.$newNameInterface], 200);
        }
        else{
            return response()->json(['error' => 'Lors du changement de nom'], 500);
        }
    }

    public function deleteNameSoftware(Request $request){

        $validator = Validator::make($request->all(), [
            'nameInterface' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $nameInterface = $request->nameInterface;

        $softwarename = Software::all()->where('name',$nameInterface)->first();
        if ($softwarename){
            $idSoftware = $softwarename->id;
            if ($idSoftware){
                $idInterfaceSoftware = $softwarename->interface_software_id;
                if ($idInterfaceSoftware){
                    $delinterfaceSoftware = InterfaceSoftware::find($idInterfaceSoftware)->delete();
                }
                $delSoftware = Software::find($idSoftware)->delete();
                return response()->json(['message' => 'l\'interface a été supprimé'], 200);
            }
            else{
                return response()->json(['message' => 'L\interface n\'existe pas.'], 404);
            }
        }
        else{
            return response()->json(['error' => 'L\interface n\'a pas été trouvé.'], 500);
        }
    }
}
