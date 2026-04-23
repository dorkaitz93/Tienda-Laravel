<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductObserver
{
    /**
     * Se ejecuta ANTES de que el producto se cree en la BD.
     */
    public function creating(Product $product): void
    {        
    
        $product->name = ucfirst($product->name);

        if (!$product->slug) {
            $product->slug = \Illuminate\Support\Str::slug($product->name) . '-' . rand(1000, 9999);
        }
    }

    public function updating(Product $product): void
    {
        if ($product->isDirty('name')) {
            $product->slug = Str::slug($product->name) . '-' . $product->id;
        }
    }
}