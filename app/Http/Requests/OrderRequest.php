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
            'shipping_address' => 'required|string|min:10|max:255',
            'contact_email'    => 'required|email',
            'items'            => 'required|array|min:1',// Validamos que 'items' sea un array y no venga vacío
            // Validamos cada objeto dentro del array 'items'
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ];
    }
    public function messages(): array
    {
        return [
            'shipping_address.required' => 'La dirección de envío es obligatoria.',
            'shipping_address.min'      => 'La dirección parece demasiado corta, añade más detalles.',
            'contact_email.email'       => 'El formato del correo electrónico no es válido.',
            'items.required'            => 'No puedes realizar un pedido sin productos.',
            'items.*.product_id.exists' => 'Uno de los productos seleccionados no existe en nuestro catálogo.',
            'items.*.quantity.min'      => 'La cantidad mínima por producto debe ser al menos 1.',
        ];
    }
}
