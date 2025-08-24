<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserFormRequest extends FormRequest
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
            //
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "role"=>"required|in:admin,user",
            "job"=>"nullable|string|max:255",
            "phone"=>"nullable|string|max:15",

        ];[
            "name.required" => "Le nom est requis",
            "email.required" => "L'email est requis",
            "email.unique" => "L'email existe déjà",
            "role.required" => "Le rôle est requis",
            "job.string" => "Le job doit être une chaîne de caractères",
            "phone.string" => "Le téléphone doit être une chaîne de caractères",
            "phone.max" => "Le téléphone doit contenir au maximum 15 caractères",
        ];
    }
}
