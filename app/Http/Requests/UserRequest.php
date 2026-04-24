<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends ApiFormRequest
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
    return[ 
        'name'     => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'],
        'email'    => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'rol'      => 'nullable|string|in:admin,cliente',
        
        ];
    }

    public function messages()
    {
        return [
            //name
            'name.required'     => 'El nombre es obligatorio',
            'name.string'       => 'El nombre debe ser texto válido',
            'name.max'          => 'El nombre no puede superar los 255 caracteres.',
            'name.regex'        => 'El nombre solo puede contener letras y espacios',

            //email

            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.string'      => 'El correo electrónico debe ser texto válido.',
            'email.email'       => 'El formato del correo electrónico no es válido.',
            'email.max'         => 'El correo electrónico no puede superar los 255 caracteres.',
            'email.unique'      => 'Este correo electrónico ya está registrado',

            //password
            'password.required' => 'La contraseña es obligatoria.',
            'password.string'   => 'La contraseña debe ser texto válido.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres',

            //rol
            'rol.string'        => 'El rol debe ser texto válido.',
            'rol.in'            => 'El rol seleccionado no es válido.',
        ];
    }
}
