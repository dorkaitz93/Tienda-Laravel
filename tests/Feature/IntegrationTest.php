<?php

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Event;
use App\Events\ProductOutOfStock;

it('admin crea stock, cliente compra todo y salta la alarma de stock', function () {

    Event::fake([\App\Events\ProductOutOfStock::class]);

    $admin = User::create(['name' => 'El Jefe', 'email' => 'admin@okin.eus', 'password' => bcrypt('123456'), 'rol' => 'admin']);
    $tokenAdmin = JWTAuth::fromUser($admin);

    $categoria = Category::create(['name' => 'Especialidades', 'slug' => 'especialidades']);

    // CORREGIDO: Usando withHeaders y array
    $responseAdmin = $this->withHeaders(['Authorization' => 'Bearer ' . $tokenAdmin])
        ->postJson('/api/products', [
            'category_id' => $categoria->id,
            'name'        => 'Txapata Artesana',
            'description' => 'Recién salida del horno',
            'price'       => 1.50,
            'stock'       => 2 
        ]);
        
    $responseAdmin->assertStatus(201);
    $productId = $responseAdmin->json('data.id'); // Guardamos el ID de la txapata creada

    $cliente = User::create(['name' => 'Dorki Hambriento', 'email' => 'dorki@cliente.com', 'password' => bcrypt('123456'), 'rol' => 'cliente']);
    $tokenCliente = JWTAuth::fromUser($cliente);

    // CORREGIDO: Usando withHeaders y array
    $responseCompra = $this->withHeaders(['Authorization' => 'Bearer ' . $tokenCliente])
        ->postJson('/api/orders', [
            'shipping_address' => 'Casa de Dorki, País Vasco',
            'contact_email'    => 'dorki@cliente.com',
            'items' => [
                [
                    'product_id' => $productId, 
                    'quantity'   => 2 // Compra todo el stock
                ]
            ]
        ]);

    $responseCompra->assertStatus(201)
                   ->assertJsonFragment(['total' => 3.00]); // 2 * 1.50

    $this->assertDatabaseHas('products', [
        'id'    => $productId,
        'stock' => 0
    ]);
    
    Event::assertDispatched(ProductOutOfStock::class, function ($event) use ($productId) {
        return $event->product->id === $productId;
    });
});