<?php
uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Models\Product;
use App\Models\Category;

it('crea un producto y el observer formatea el nombre y genera el slug', function () {

        $category = Category::create([
        'name' => 'Maquetas de Rascacielos',
        'slug' => 'maquetas-de-rascacielos' 
    ]);

    // 2. Creamos el producto
    $product = Product::create([
        'name' => 'rascacielos de cristal',
        'category_id' => $category->id,
        'description' => 'Pieza única',
        'price' => 100,
        'stock' => 10,
    ]);

    // 3. Verificamos que el Observer ha trabajado
    expect($product->name)->toBe('Rascacielos de cristal') 
        ->and($product->slug)->not->toBeNull()
        ->and($product->slug)->toContain('rascacielos-de-cristal');
});