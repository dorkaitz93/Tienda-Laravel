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
            'category_id' => 'required|integer|exists:categories,id',
            'name'        => 'required|string|min:3|max:50',
            'description' => 'required|string|max:1000',
            'price'       => 'required|numeric|min:1',
            'stock'       => 'required|integer|min:0',
            //opcionales
            'size'        => 'nullable|string|max:50',
            'material'    => 'nullable|string|max:50',    
            'dimensions'  => 'nullable|string|max:50',   
        ];
    }
    public function messages(): array
    {
        return [
            //category_id
            'category_id.required' => 'Debes seleccionar una categoría para el producto.',
            'category_id.integer'  => 'El identificador de la categoría debe ser un número.',
            'category_id.exists'   => 'La categoría seleccionada no existe en nuestra base de datos.',

            //name
            'name.required'        => 'El nombre del producto es obligatorio.',
            'name.string'          => 'El nombre debe ser una cadena de texto válida.',
            'name.min'             => 'El nombre es demasiado corto (mínimo 3 caracteres).',
            'name.max'             => 'El nombre es demasiado largo (máximo 50 caracteres).',

            // Mensajes para description
            'description.required' => 'La descripción del producto es obligatoria.',
            'description.string'   => 'La descripción debe ser texto válido.',
            'description.max'      => 'La descripción no puede superar los 1000 caracteres.',


            // Mensajes para price
            'price.required'       => 'El precio del producto es obligatorio.',
            'price.numeric'        => 'El precio debe ser un valor numérico.',
            'price.min'            => 'El precio mínimo permitido es 1.',
            
            //stock
            'stock.required'       => 'La cantidad de stock es obligatoria.',
            'stock.integer'        => 'El stock debe ser un número entero (no puedes vender fracciones de producto).',
            'stock.min'            => 'El stock no puede ser un número negativo.',

            'size.string'          => 'El formato del tamaño no es válido, debe ser texto.',
            'size.max'          => 'El tamaño no puede tener mas de 50 caracteres',

            'material.string'      => 'El formato del material no es válido, debe ser texto.',
            'material.max'      => 'El material no puede tener mas de 50 caracteres',

            'dimensions.string'    => 'El formato de las dimensiones no es válido, debe ser texto.',
            'dimensions.max'    => 'Las Dimensiones no pueden tener mas de 50 caracteres',
        ];
    }
}
