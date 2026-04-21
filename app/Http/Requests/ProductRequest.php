<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends ApiFormRequest
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
            //requeridos
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            //opcionales
            'size'        => 'nullable|string',
            'material'    => 'nullable|string',    
            'dimensions'  => 'nullable|string',   
        ];
    }
    public function messages(): array
    {
        return [
            'category_id.exists' => 'La categoría seleccionada no es válida.',
            'name.required'      => 'El nombre del producto es obligatorio.',
            'price.numeric'      => 'El precio debe ser un número.',
        ];
    }
}
