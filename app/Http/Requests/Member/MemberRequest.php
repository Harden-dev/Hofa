<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

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
            //
            "name" => "required",
            "email" => "required|email",
            "gender" => "required|in:M,F",
            "phone" => "required",
            "marital_status" => "required",
            "professional_profile" => "nullable",
            "benevolent_type_id" => "required",
            "is_benevolent" => "nullable|boolean",
            "residence" => "required",
            "benevolent_experience" => "nullable",
        ];
    }
}
