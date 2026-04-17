<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends ApiFormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ];
    }

    public function messages()
    {
        return [
            'email.required'    => 'El correo es obligatorio para entrar',
            'email.email'       => 'Introduce un formato de correo válido',
            'password.required' => 'Debes introducir la contraseña',
        ];
    }
}
