<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\ConvertController2;
use App\Models\Misc\InterfaceSoftware;
use App\Models\Misc\Software;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InterfaceSoftwareController extends ConvertController
{
    public function getInterfaceSoftware($request){

        // $validator = Validator::make($request->all(), [
        //     'nomInterface' => 'required|string',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }  

        $softwareName = $request['nomInterface'];
        $softwaresNames = Software::all()->where('name',$softwareName)->first();

        if ($softwaresNames !== null){
            $idSoftware = $softwaresNames->interface_software_id;
            $interfaceSoftwares = InterfaceSoftware::all()->where('id',$idSoftware)->first();
            // return response()->json($interfaceSoftwares, 200);
            return $interfaceSoftwares;       
        }
        else{
            return response()->json(['message'=>'il n\'y pas d\'interface', $softwaresNames], 400);
        }
    }

    public function createInterfaceSoftware(Request $request){

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
            'format'=> 'required|string',
            'nomInterface' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try{
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
                'format'=> $request->format,
            ];

            $softwareId = 0;
            $interfacesoftware = InterfaceSoftware::create($data);
            if ($interfacesoftware ){
                $softwareId = $interfacesoftware->id;
            }

            $softwareName = $request->nomInterface;

            $softwaresNames = Software::all()->where('name',$softwareName)->first();
            // dd($softwaresNames);
            if ($softwaresNames){
                $softwareuptade = $softwaresNames->update(['interface_software_id'=>$softwareId]);
                $softwares = $softwaresNames;
            }
            else{
                $softwares = Software::create(['name'=>$softwareName,'interface_software_id'=>$softwareId]);
            }
            
            return response()->json(['message' => 'Création de l\'interface réussie', 'software' =>  $softwares], 200);
        }
        catch (\Exception $e) {
            //  dd($e);
            return response()->json(['error' => 'Une erreur est survenue lors de la création de l\'interface.'], 500);
        }
    }   

    public function updateInterfaceSoftware(Request $request){

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
            'format'=> 'required|string',
            'nomInterface' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try{
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
                'format'=> $request->format,
            ];

            $softwareName = $request->nomInterface;

            $softwaresNames = Software::all()->where('name',$softwareName)->first();
            $idSoftwares = $softwaresNames->interface_software_id;

            $softwareuptade = InterfaceSoftware::where('id',$idSoftwares)->update($data);
       
            return response()->json(['message' => 'Mise à jour faite', 'software' =>  $softwareName], 200);
        }
        catch (\Exception $e) {
            //  dd($e);
            return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour.'], 500);
        }
    }

    public function deleteInterfaceSoftware(Request $request){

        $validator = Validator::make($request->all(), [
            'nomInterface' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }  
        
        $softwareName = $request->nomInterface;
        $softwaresNames = Software::all()->where('name',$softwareName)->first();
        if ($softwaresNames){
            $idSoftware = $softwaresNames->interface_software_id;
            if ($idSoftware){
                $interfaceSoftware = InterfaceSoftware::find($idSoftware)->delete();
                return response()->json(['message' => 'l\'interface a été supprimé'], 200);
            }
            else{
                return response()->json(['message' => 'L\interface n\'existe pas.'], 404);
            }
        }
        else{
            return response()->json(['error' => 'L\interface n\'a pas été trouvé.'], 404);
        }

    } 
}