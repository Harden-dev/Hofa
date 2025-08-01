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
        $memberId = $this->route('member')?->id;

        return [
            'type' => ['required', Rule::in(['individual', 'company'])],
            'name' => 'nullable|string|max:255',
            'bossName' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('members', 'email')->ignore($memberId),
            ],
            'phone' => 'required|string|max:20',
            'gender' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'matrimonial' => 'nullable|string|max:100',
            'is_volunteer' => 'boolean',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'is_rejected' => 'boolean',
            'habit' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'job' => 'nullable|string|max:255',
            'volunteer' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'web' => 'nullable|url|max:255',
            'activity' => 'nullable|string|max:500',
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
            'type.required' => 'Le type de membre est requis.',
            'type.in' => 'Le type doit être "individual" ou "company".',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'phone.required' => 'Le téléphone est requis.',
            'phone.max' => 'Le téléphone ne peut pas dépasser 20 caractères.',
            'gender.max' => 'Le genre ne peut pas dépasser 50 caractères.',
            'nationality.max' => 'La nationalité ne peut pas dépasser 100 caractères.',
            'matrimonial.max' => 'Le statut matrimonial ne peut pas dépasser 100 caractères.',
            'habit.max' => 'Le lieu de résidence ne peut pas dépasser 255 caractères.',
            'job.max' => 'La profession ne peut pas dépasser 255 caractères.',
            'volunteer.max' => 'La description du bénévolat ne peut pas dépasser 255 caractères.',
            'origin.max' => 'L\'origine ne peut pas dépasser 255 caractères.',
            'web.url' => 'Le site web doit être une URL valide.',
            'web.max' => 'Le site web ne peut pas dépasser 255 caractères.',
            'activity.max' => 'Les activités ne peuvent pas dépasser 500 caractères.',
        ];
    }
}
