<?php

namespace App\Http\Resources\Member;

use App\Http\Resources\TypeBenevole\TypeBenevoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'professional_profile' => $this->professional_profile,
            'residence' => $this->residence,
            'benevolent_experience' => $this->benevolent_experience,
            'is_benevolent' => $this->is_benevolent,    
            'slug' => $this->slug,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'type_benevole' => $this->whenLoaded('benevolent_type', function() {
                return new TypeBenevoleResource($this->benevolent_type);
            }),
        ];
    }
}
