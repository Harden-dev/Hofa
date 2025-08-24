<?php

namespace App\Http\Requests\Enfiler;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnfilerFormRequest extends FormRequest
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
            'type' => ['required', Rule::in(['individual', 'company'])],
            'name' => 'nullable|string|max:255',
            'bossName' => 'nullable|string|max:255',
            'donationType' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'motivation' => 'nullable|string|max:1000',
            'is_active' => 'boolean',

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
            'type.required' => 'Le type de donateur est requis.',
            'type.in' => 'Le type doit être "individual" ou "company".',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'bossName.max' => 'Le nom du responsable ne peut pas dépasser 255 caractères.',
            'donationType.required' => 'Le type de don est requis.',
            'donationType.max' => 'Le type de don ne peut pas dépasser 100 caractères.',
            'phone.required' => 'Le téléphone est requis.',
            'phone.max' => 'Le téléphone ne peut pas dépasser 20 caractères.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.max' => 'L\'email ne peut pas dépasser 255 caractères.',
            'motivation.max' => 'La motivation ne peut pas dépasser 1000 caractères.',
        ];
    }
}
