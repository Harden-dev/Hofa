<?php

namespace App\Http\Resources\Dons;

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
            'slug' => $this->slug,
            'type' => $this->type,
            'name' => $this->name,
            'bossName' => $this->bossName,
            'donationType' => $this->donationType,
            'phone' => $this->phone,
            'email' => $this->email,
            'motivation' => $this->motivation,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->rejected_at,
            'rejection_reason' => $this->rejection_reason,
            'is_approved' => $this->is_approved,
            'is_rejected' => $this->is_rejected,

            // Accesseurs calculés
            'full_name' => $this->full_name,
            'display_name' => $this->display_name,
            'status_text' => $this->status_text,
            'donation_type_text' => $this->donation_type_text,
            'type_text' => $this->type_text,
        ];
    }
}
