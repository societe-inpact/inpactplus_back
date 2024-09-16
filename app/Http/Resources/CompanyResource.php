<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'referent' => new UserResource($this->referent),
            'notes' => $this->notes,
            'modules' => $this->modules->map(function ($module) {
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'label' => $module->label,
                ];
            }),
            'employees' => EmployeeResource::collection($this->employees),
            'folders' => CompanyFolderResource::collection($this->folders),
        ];
    }
}
