<?php

namespace App\Http\Resources\Member;

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
            'slug' => $this->slug,
            'type' => $this->type,
            'name' => $this->name,
            'bossName' => $this->bossName,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'date_naissance' => $this->date_naissance,
            'age' => $this->age,
            'date_naissance_formatted' => $this->date_naissance_formatted,
            'nationality' => $this->nationality,
            'matrimonial' => $this->matrimonial,
            'is_volunteer' => $this->is_volunteer,
            'is_active' => $this->is_active,
            'habit' => $this->habit,
            'bio' => $this->bio,
            'job' => $this->job,
            'volunteer' => $this->volunteer,
            'origin' => $this->origin,
            'web' => $this->web,
            'activity' => $this->activity,
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->rejected_at,
            'rejection_reason' => $this->rejection_reason,
            'is_approved' => $this->is_approved,
            'is_rejected' => $this->is_rejected,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Accesseurs calculés
            'full_name' => $this->full_name,
            'display_name' => $this->display_name,
            'status_text' => $this->status_text,
            'volunteer_status_text' => $this->volunteer_status_text,
            'approval_status' => $this->approval_status,
            'approval_status_text' => $this->approval_status_text,
        ];
    }
}
