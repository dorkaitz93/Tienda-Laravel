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
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|max:128'
        ];
    }

    public function messages()
    {
        return [
            //email
            'email.required'    => 'El correo es obligatorio para entrar',
            'email.email'       => 'Introduce un formato de correo válido',
            'email.max'         => 'El email introducido no puede tener mas de 255 caracteres',
            'password.required' => 'Debes introducir la contraseña',
            'password.max'      => 'La contraseña no puede tener mas de 128 caracteres',
            'password.min'      => 'la contraseña no puede ser inferior a 8 caracteres',

        ];
    }
}
