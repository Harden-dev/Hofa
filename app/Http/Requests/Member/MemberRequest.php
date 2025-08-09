<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['individual', 'company'])],
            'name' => ['nullable', 'string', 'max:255'],
            'bossName' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('members')->ignore($this->member)],
            'gender' => ['nullable', 'string', 'max:50'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'matrimonial' => ['nullable', 'string', 'max:50'],
            'is_volunteer' => ['boolean'],
            'is_active' => ['boolean'],
            'habit' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'job' => ['nullable', 'string', 'max:255'],
            'volunteer' => ['nullable', 'string', 'max:255'],
            'origin' => ['nullable', 'string', 'max:255'],
            'web' => ['nullable', 'url', 'max:255'],
            'activity' => ['nullable', 'string', 'max:500'],
            'is_approved' => ['boolean'],
            'is_rejected' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type est requis',
            'type.in' => 'Le type doit être individual ou company',
            'name.string' => 'Le nom doit être une chaîne de caractères',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères',
            'bossName.string' => 'Le nom du responsable doit être une chaîne de caractères',
            'bossName.max' => 'Le nom du responsable ne peut pas dépasser 255 caractères',
            'phone.required' => 'Le téléphone est requis',
            'phone.string' => 'Le téléphone doit être une chaîne de caractères',
            'phone.max' => 'Le téléphone ne peut pas dépasser 20 caractères',
            'email.required' => 'L\'email est requis',
            'email.email' => 'L\'email doit être une adresse email valide',
            'email.max' => 'L\'email ne peut pas dépasser 255 caractères',
            'email.unique' => 'Cet email est déjà utilisé',
            'gender.required' => 'Le genre est requis',
            'gender.string' => 'Le genre doit être une chaîne de caractères',
            'gender.max' => 'Le genre ne peut pas dépasser 50 caractères',
            'date_naissance.date' => 'La date de naissance doit être une date valide',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui',
            'nationality.required' => 'La nationalité est requise',
            'nationality.string' => 'La nationalité doit être une chaîne de caractères',
            'nationality.max' => 'La nationalité ne peut pas dépasser 100 caractères',
            'matrimonial.required' => 'Le statut matrimonial est requis',
            'matrimonial.string' => 'Le statut matrimonial doit être une chaîne de caractères',
            'matrimonial.max' => 'Le statut matrimonial ne peut pas dépasser 50 caractères',
            'is_volunteer.boolean' => 'Le statut bénévole doit être vrai ou faux',
            'is_active.boolean' => 'Le statut actif doit être vrai ou faux',
            'habit.string' => 'L\'habitat doit être une chaîne de caractères',
            'habit.max' => 'L\'habitat ne peut pas dépasser 255 caractères',
            'bio.string' => 'La biographie doit être une chaîne de caractères',
            'bio.max' => 'La biographie ne peut pas dépasser 1000 caractères',
            'job.string' => 'Le métier doit être une chaîne de caractères',
            'job.max' => 'Le métier ne peut pas dépasser 255 caractères',
            'volunteer.string' => 'Le statut bénévole doit être une chaîne de caractères',
            'volunteer.max' => 'Le statut bénévole ne peut pas dépasser 255 caractères',
            'origin.string' => 'L\'origine doit être une chaîne de caractères',
            'origin.max' => 'L\'origine ne peut pas dépasser 255 caractères',
            'web.url' => 'Le site web doit être une URL valide',
            'web.max' => 'Le site web ne peut pas dépasser 255 caractères',
            'activity.string' => 'L\'activité doit être une chaîne de caractères',
            'activity.max' => 'L\'activité ne peut pas dépasser 500 caractères',
            'is_approved.boolean' => 'Le statut d\'approbation doit être vrai ou faux',
            'is_rejected.boolean' => 'Le statut de rejet doit être vrai ou faux',
        ];
    }
}
