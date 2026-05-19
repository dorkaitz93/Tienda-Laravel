<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'id'          => $this->id,
        'name'        => $this->name,
        'price'       => $this->price,
        'description' => $this->description,
        'size'        => $this->size,
        'dimensions'  =>$this->dimensions,
        'stock'       => $this->stock,       // <- Añadido para gestionar inventario
        'slug'        => $this->slug,
        // Generamos la URL segura. Si no hay imagen, enviamos una por defecto.
        'image'       => $this->image 
            ? asset('storage/' . $this->image) 
            : asset('storage/products/default.png'),
        'category'    => $this->category->name,
        'category_id' => $this->category_id,
    ];
    }
}
