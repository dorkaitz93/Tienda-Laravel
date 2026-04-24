<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends ApiFormRequest
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
            'shipping_address' => ['required', 'string', 'min:10', 'max:255', 'regex:/^[a-zA-Z0-9\s,.\-ºªáéíóúÁÉÍÓÚñÑ]+$/u'],
            'contact_email'    => 'required|email|max:255',
            'items'            => 'required|array|min:1',// Validamos que 'items' sea un array y no venga vacío
            // Validamos cada objeto dentro del array 'items'
            'items.*.product_id' => 'required|integer|exists:products,id|distinct',
            'items.*.quantity'   => 'required|integer|min:1|max:50',
        ];
    }
    public function messages(): array
    {
        return [
            //address
            'shipping_address.required' => 'La dirección de envío es obligatoria.',
            'shipping_address.min'      => 'La dirección parece demasiado corta, añade más detalles.',
            'shipping_address.max'      => 'La dirección es demasiado larga (máximo 255 caracteres).',
            'shipping_address.regex'    => 'La dirección contiene caracteres no válidos.',

            //email
            'contact_email.required'    => 'El correo electrónico de contacto es obligatorio.',
            'contact_email.email'       => 'El formato del correo electrónico no es válido.',
            'contact_email.max'         => 'El correo electrónico no puede superar los 255 caracteres.',
            
            //items
            'items.required'            => 'No puedes realizar un pedido sin productos.',
            'items.array'               => 'El formato del carrito no es válido.',
            'items.min'                 => 'Debes añadir al menos un producto al carrito.',

            //product_id
            'items.*.product_id.required' => 'Falta el identificador de uno de los productos.',
            'items.*.product_id.exists'   => 'Uno de los productos seleccionados no existe en nuestro catálogo.',
            'items.*.product_id.distinct' => 'Has añadido el mismo producto varias veces. Por favor, agrupa la cantidad en una sola línea.',

            //quantity
            'items.*.quantity.required'   => 'Debes especificar la cantidad para cada producto.',
            'items.*.quantity.min'        => 'La cantidad mínima por producto debe ser al menos 1.',
            'items.*.quantity.max'        => 'No puedes pedir más de 50 unidades del mismo producto por pedido.',

            
        ];
    }
}
