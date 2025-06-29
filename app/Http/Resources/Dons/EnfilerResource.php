<?php

namespace App\Http\Resources\Dons;

use App\Http\Resources\TypeDons\EnfilerTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnfilerResource extends JsonResource
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
            'phone' => $this->phone,
            'email' => $this->email,
            'motivation' => $this->motivation,
            'is_active' => $this->is_active,
            'type_dons' => $this->whenLoaded('type_enfiler', function() {
                return new EnfilerTypeResource($this->type_enfiler);
            }),

            
            
        ];
    }
}
