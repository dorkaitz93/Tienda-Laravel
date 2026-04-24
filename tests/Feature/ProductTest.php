<?php

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Event;
use App\Events\ProductOutOfStock;
use Tymon\JWTAuth\Facades\JWTAuth;

it('actualiza un producto y dispara el evento de stock cero', function () {
    
    // 1. Fake de eventos
    Event::fake();

    // 2. Creamos el usuario con TU columna 'rol' y valor 'admin'
    $admin = User::create([
        'name' => 'Admin Dorki',
        'email' => 'admin@test.com',
        'password' => bcrypt('123456'),
        'rol' => 'admin'
    ]);
    
    $token = JWTAuth::fromUser($admin);

    // 3. Creamos categoría y producto inicial
    $category = Category::create(['name' => 'Rascacielos', 'slug' => 'rascacielos']);
    $product = Product::create([
        'name' => 'Torre Antigua',
        'category_id' => $category->id,
        'description' => 'Vieja torre',
        'price' => 50.00,
        'stock' => 10,
        'slug' => 'torre-antigua'
    ]);

    // Llamamos al Update
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->putJson("/api/products/{$product->id}", [
            'name' => 'Torre Moderna',
            'category_id' => $category->id,
            'description' => 'Torre actualizada',
            'price' => 99.99,
            'stock' => 0 // Bajamos stock a 0 para que salte el evento
        ]);

    // 5. VERIFICACIONES
    
   
    $response->assertStatus(200);

    // Verificamos que los datos se han guardado de verdad 
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Torre Moderna',
        'stock' => 0
    ]);

    // C. Verificamos que el evento se lanzó
    Event::assertDispatched(ProductOutOfStock::class);
});