<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyFolderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $folderId = $this->id;
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'folder_number' => $this->folder_number,
            'folder_name' => $this->folder_name,
            'referent' => new UserResource($this->referent),
            'siret' => $this->siret,
            'siren' => $this->siren,
            'notes' => $this->notes,
            'interfaces' => $this->interfaces,
            'mappings' => $this->mappings,
            'employees' => EmployeeResource::collection($this->employees),
            'modules' => ModuleResource::collection($this->modules->map(function ($module) use ($folderId) {
                $permissions = $module->userPermissionsForFolder($folderId)->get();
                $module->setRelation('userPermissions', $permissions);
                return $module;
            })),
        ];
    }
}
