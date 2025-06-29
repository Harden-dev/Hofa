<?php

namespace App\Http\Requests\Enfiler;

use Illuminate\Foundation\Http\FormRequest;

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
            //
            "name" => "required",
            "email" => "required",
            "phone" => "required",
            "enfiler_type_id" => "required",
            "motivation" => "nullable",

        ];
    }
}
