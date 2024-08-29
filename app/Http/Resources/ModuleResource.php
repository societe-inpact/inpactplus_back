<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userPermissionsAvailable = $this->whenLoaded('userPermissions') && $this->userPermissions->isNotEmpty();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'user_permissions' => $userPermissionsAvailable ? PermissionResource::collection($this->userPermissions) : [],
        ];
    }
}
