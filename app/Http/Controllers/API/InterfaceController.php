<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absences\Absence;
use App\Models\Misc\InterfaceSoftware;
use App\Models\Misc\InterfaceMapping;
use App\Rules\CustomRubricRule;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InterfaceController extends Controller
{
    use JSONResponseTrait;

    public function getInterfaces()
    {
        $softwares = InterfaceSoftware::all();
        return $this->successResponse($softwares);
    }

    public function createInterface(Request $request)
    {
        $this->authorize('create_interface', InterfaceSoftware::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'interface_mapping_id' => 'nullable|exists:interface_mapping.id',
        ]);

        $isInterfaceExists = InterfaceSoftware::where('name', $validated['name'])->exists();

        if ($isInterfaceExists) {
            return $this->errorResponse('Interface déjà existante', 403);
        }

        $interface = InterfaceSoftware::create([
            'name' => $validated['name'],
            'interface_mapping_id' => $request->input('interface_mapping_id', null),
        ]);
        if ($interface) {
            return $this->successResponse($interface, 'Interface créée avec succès', 201);
        }
        return $this->errorResponse('Impossible de créer l\'interface', 500);
    }

    public function updateInterface(Request $request, $id)
    {
        $this->authorize('update_interface', InterfaceSoftware::class);

        $software = InterfaceSoftware::findOrFail($id);
        $software->name = $request->name;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        if ($software->save()) {
            return $this->successResponse('', 'Le nom a été changé en ' . $request->name);
        } else {
            return $this->errorResponse('Erreur lors du changement de nom', 500);
        }
    }

    public function deleteInterface($id)
    {
        $this->authorize('delete_interface', InterfaceSoftware::class);

        $software = InterfaceSoftware::findOrFail($id);

        if ($software->delete()) {
            return $this->successResponse('', 'L\'interface a été supprimée avec succès');
        } else {
            return $this->errorResponse('L\'interface n\'existe pas', 404);
        }
    }
}
