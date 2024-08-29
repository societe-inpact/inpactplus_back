<?php

namespace App\Http\Controllers\API;

use App\Models\Misc\InterfaceMapping;
use App\Models\Misc\InterfaceSoftware;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InterfaceMappingController extends ConvertController
{
    use JSONResponseTrait;

    public function getInterfaceMapping($id){
        // récupère l'interface software

        $InterfaceMappingId = InterfaceMapping::findOrFail($id);
        if ($InterfaceMappingId){
            return InterfaceMapping::findOrFail($id);
        }
        else{
            return response()->json(['message'=>'il n\'y pas d\'interface', $InterfaceMappingId], 400);
        }
    }

    public function createInterfaceMapping(Request $request){

        $validator = Validator::make($request->all(), [
            'employee_number' => 'required|integer',
            'rubric' => 'required|integer',
            'value' => 'required|integer',
            'start_date' => 'nullable|integer',
            'end_date'=> 'nullable|integer',
            'hj'=> 'nullable|integer',
            'percentage_tp'=> 'nullable|integer',
            'period' => 'nullable|integer',
            'separator_type' => 'nullable|string',
            'extension'=> 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'software_name' => $request->software_name || null,
            'employee_number' => $request->employee_number,
            'rubric' => $request->rubric,
            'value' => $request->value,
            'start_date' => $request->start_date,
            'end_date'=> $request->end_date,
            'hj'=> $request->hj,
            'percentage_tp'=> $request->percentage_tp,
            'period' => $request->period,
            'separator_type' => $request->separator_type,
            'extension'=> $request->extension,
        ];

        $softwareName = $request->software_name;
        $softwareNamesExisting = InterfaceSoftware::all()->where('name', '=', $softwareName);

        if ($softwareNamesExisting->isNotEmpty()){
            return response()->json(['error' => 'Le nom de l\'interface existe déjà'], 500);
        }else{
            $interfaceMapping = InterfaceMapping::create($data);
            if ($interfaceMapping ){
                $softwareId = $interfaceMapping->id;
                $software = InterfaceSoftware::create(['name'=> $softwareName, 'interface_mapping_id'=> $softwareId]);
                return response()->json(['message' => 'Création de l\'interface réussie', 'data' =>  $software], 200);
            }
        }
        return response()->json(['error' => 'Une erreur est survenue lors de la création de l\'interface.'], 500);
    }

    public function updateInterfaceMapping(Request $request, $id){

        $validator = Validator::make($request->all(), [
            'employee_number' => 'required|integer',
            'rubric' => 'required|integer',
            'value' => 'required|integer',
            'start_date' => 'nullable|integer',
            'end_date'=> 'nullable|integer',
            'hj'=> 'nullable|integer',
            'percentage_tp'=> 'nullable|integer',
            'period' => 'nullable|integer',
            'separator_type' => 'nullable|string',
            'extension'=> 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'employee_number' => $request->employee_number,
            'rubric' => $request->rubric,
            'value' => $request->value,
            'start_date' => $request->start_date,
            'end_date'=> $request->end_date,
            'hj'=> $request->hj,
            'percentage_tp'=> $request->percentage_tp,
            'period' => $request->period,
            'separator_type' => $request->separator_type,
            'extension'=> $request->extension,
        ];

        $interface = InterfaceMapping::findOrFail($id);
        if ($interface->update($data)){
            return response()->json(['message' => 'Mise à jour de l\'interface réussie'], 200);
        }
        return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour de l\'interface'], 500);
    }

    public function deleteInterfaceMapping($id){

        $interfaceMapping = InterfaceMapping::findOrFail($id);
        if ($interfaceMapping->delete()){
            return response()->json(['message' => 'L\'interface a été supprimée'], 200);
        }
        return response()->json(['message' => 'L\'interface n\'existe pas.'], 404);
    }
}
