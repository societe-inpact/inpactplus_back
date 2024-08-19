<?php

namespace App\Http\Controllers\API;

use App\Models\Misc\InterfaceMapping;
use App\Models\Misc\InterfaceSoftware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InterfaceMappingController extends ConvertController
{
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
            'matricule' => 'required|integer',
            'rubrique' => 'required|integer',
            'valeur' => 'required|integer',
            'datedeb' => 'nullable|integer',
            'datefin'=> 'nullable|integer',
            'hj'=> 'nullable|integer',
            'pourcentagetp'=> 'nullable|integer',
            'periode' => 'nullable|integer',
            'separateur' => 'nullable|string',
            'extension'=> 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'software_name' => $request->software_name || null,
            'colonne_matricule' => $request->matricule,
            'colonne_rubrique' => $request->rubrique,
            'colonne_valeur' => $request->valeur,
            'colonne_datedeb' => $request->datedeb,
            'colonne_datefin'=> $request->datefin,
            'colonne_hj'=> $request->hj,
            'colonne_pourcentagetp'=> $request->pourcentagetp,
            'colonne_periode' => $request->periode,
            'type_separateur' => $request->separateur,
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
            }
            $softwares = InterfaceSoftware::create(['name'=> $softwareName, 'interface_mapping_id'=> $softwareId]);
            return response()->json(['message' => 'Création de l\'interface réussie', 'software' =>  $softwares], 200);
        }

        return response()->json(['error' => 'Une erreur est survenue lors de la création de l\'interface.'], 500);
    }

    public function updateInterfaceMapping(Request $request, $id){

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string',
            'matricule' => 'required|integer',
            'rubrique' => 'required|integer',
            'valeur' => 'required|integer',
            'datedeb' => 'nullable|integer',
            'datefin'=> 'nullable|integer',
            'hj'=> 'nullable|integer',
            'pourcentagetp'=> 'nullable|integer',
            'periode' => 'nullable|integer',
            'separateur' => 'nullable|string',
            'extension'=> 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'colonne_matricule' => $request->matricule,
            'colonne_rubrique' => $request->rubrique,
            'colonne_valeur' => $request->valeur,
            'colonne_datedeb' => $request->datedeb,
            'colonne_datefin'=> $request->datefin,
            'colonne_hj'=> $request->hj,
            'colonne_pourcentagetp'=> $request->pourcentagetp,
            'colonne_periode' => $request->periode,
            'type_separateur' => $request->separateur,
            'extension'=> $request->extension,
        ];

        if (InterfaceMapping::findOrFail($id)->update($data)){
            return response()->json(['message' => 'Mise à jour de l\'interface réussie'], 200);
        }
        return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour de l\'interface'], 500);
    }

    public function deleteInterfaceMapping($id){

        $interfaceMapping = InterfaceMapping::findOrFail($id);
        if ($interfaceMapping){
            $interfaceMapping->delete();
            return response()->json(['message' => 'L\'interface a été supprimée'], 200);
        }
        return response()->json(['message' => 'L\'interface n\'existe pas.'], 404);
    }
}
