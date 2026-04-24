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
    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
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

it('genera un slug automáticamente al crear un producto', function () {

    $category = Category::create(['name' => 'Dulces', 'slug' => 'dulces']);
    
  
    $product = Product::create([
        'name'        => 'Palmerita de Chocolate',
        'category_id' => $category->id,
        'description' => 'Muy rica',
        'price'       => 1.50,
        'stock'       => 20
    ]);

    
    $this->assertStringStartsWith('palmerita-de-chocolate', $product->slug);
});

it('evita colisiones de slug asegurando que sean únicos', function () {
   

    $category = Category::create(['name' => 'Dulces', 'slug' => 'dulces']);

  
    $product1 = Product::create([
        'name'        => 'Palmera',
        'category_id' => $category->id,
        'description' => 'Normal',
        'price'       => 1.00,
        'stock'       => 10
    ]);

    
    $product2 = Product::create([
        'name'        => 'Palmera',
        'category_id' => $category->id,
        'description' => 'Integral',
        'price'       => 1.20,
        'stock'       => 10
    ]);


    $this->assertStringStartsWith('palmera', $product1->slug);
    $this->assertStringStartsWith('palmera', $product2->slug);
    $this->assertNotEquals($product1->slug, $product2->slug);
});

it('prohíbe a un usuario normal (cliente) gestionar productos', function () {
    /** @var \Tests\TestCase $this */
    
    // 1. Creamos un Cliente (NO admin) y un producto de prueba
    $cliente = User::create(['name' => 'Cliente', 'email' => 'cliente@test.com', 'password' => '1', 'rol' => 'cliente']);
    $token = JWTAuth::fromUser($cliente);
    
    $category = Category::create(['name' => 'Test', 'slug' => 'test']);
    $product = Product::create(['name' => 'Pan', 'category_id' => $category->id, 'description' => 'A', 'price' => 1, 'stock' => 10]);

    // 2. Intenta CREAR
    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
         ->postJson('/api/products', ['name' => 'Hacker Pan'])
         ->assertStatus(403);

    // 3. Intenta EDITAR
    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
         ->putJson('/api/products/' . $product->id, ['price' => 0.01])
         ->assertStatus(403);

    // 4. Intenta BORRAR
    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
         ->deleteJson('/api/products/' . $product->id)
         ->assertStatus(403);
});

it('permite al admin eliminar un producto', function () {
    /** @var \Tests\TestCase $this */
    
    $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => '1', 'rol' => 'admin']);
    $token = JWTAuth::fromUser($admin);
    
    $category = Category::create(['name' => 'Test', 'slug' => 'test']);
    $product = Product::create(['name' => 'Pan Duro', 'category_id' => $category->id, 'description' => 'Para tirar', 'price' => 1, 'stock' => 10]);

    // El admin lanza el DELETE
    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                     ->deleteJson('/api/products/' . $product->id);

    $response->assertStatus(200);
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

// ==========================================
// TESTS PÚBLICOS (SHOW E INDEX)
// ==========================================

it('devuelve error 404 si se intenta ver un producto que no existe', function () {
    /** @var \Tests\TestCase $this */
    
    // Cualquier usuario (incluso sin login) intenta buscar el producto ID 99999
    $response = $this->getJson('/api/products/99999');

    $response->assertStatus(404);
});

it('lista los productos públicos correctamente', function () {
    /** @var \Tests\TestCase $this */
    
    $category = Category::create(['name' => 'Test', 'slug' => 'test']);
    Product::create(['name' => 'Prod 1', 'category_id' => $category->id, 'description' => 'A', 'price' => 1, 'stock' => 10]);
    Product::create(['name' => 'Prod 2', 'category_id' => $category->id, 'description' => 'B', 'price' => 2, 'stock' => 5]);

    $response = $this->getJson('/api/products');

    $response->assertStatus(200)
             ->assertJsonCount(2, 'data'); // Suponiendo que tu index los devuelve dentro de 'data'
});

it('filtra productos por término de búsqueda y categoría', function () {
    /** @var \Tests\TestCase $this */
    
    $catPan = Category::create(['name' => 'Panadería', 'slug' => 'pan']);
    $catDulce = Category::create(['name' => 'Dulces', 'slug' => 'dulces']);

    Product::create(['name' => 'Hogaza', 'category_id' => $catPan->id, 'description' => 'Pan', 'price' => 1, 'stock' => 10]);
    Product::create(['name' => 'Croissant', 'category_id' => $catDulce->id, 'description' => 'Dulce', 'price' => 1, 'stock' => 10]);

    // 1. Probamos la búsqueda por texto (search)
    $responseSearch = $this->getJson('/api/products?search=Hogaza');
    $responseSearch->assertStatus(200)
                   ->assertJsonFragment(['name' => 'Hogaza'])
                   ->assertJsonMissing(['name' => 'Croissant']);

    // 2. Probamos el filtro por categoría (category_id)
    $responseCat = $this->getJson('/api/products?category_id=' . $catDulce->id);
    $responseCat->assertStatus(200)
                ->assertJsonFragment(['name' => 'Croissant'])
                ->assertJsonMissing(['name' => 'Hogaza']);
});