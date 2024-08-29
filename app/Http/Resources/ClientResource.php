<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'email' => $this->email,
            'civility' => $this->civility,
            'lastname' => $this->lastname,
            'firstname' => $this->firstname,
            'telephone' => $this->telephone,
            'roles' => $this->roles->pluck('name')->toArray(),
            'modules_access' => $this->modules(),
            'companies' => [
                'id' => $this->company->id,
                'name' => $this->company->name,
                'description' => $this->company->description,
                'referent' => $this->company->referent,
                'folders' => CompanyFolderResource::collection($this->folders),
            ],
        ];
    }
}
