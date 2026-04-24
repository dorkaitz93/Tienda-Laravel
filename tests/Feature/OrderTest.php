<?php

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Order;

/**
 * Función auxiliar para crear un usuario cliente y su token
 */
function createCustomerToken() {
    $user = User::create([
        'name' => 'Dorki Cliente',
        'email' => 'cliente@okin.eus',
        'password' => bcrypt('123456'),
        'rol' => 'cliente'
    ]);
    return JWTAuth::fromUser($user);
}

// --- TESTS DE FLUJO NORMAL ---

it('crea un pedido correctamente si hay stock', function () {
    
    $token = createCustomerToken();
    $category = Category::create(['name' => 'Panadería', 'slug' => 'panaderia']);
    $product = Product::create([
        'name' => 'Hogaza Okin',
        'category_id' => $category->id,
        'description' => 'Pan artesano',
        'price' => 2.50,
        'stock' => 10,
        'slug' => 'hogaza-okin'
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/orders', [
            'shipping_address' => 'Calle Mayor 1',
            'contact_email' => 'dorki@okin.eus',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2]
            ]
        ]);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 8
    ]);
});

// --- TESTS DE VALIDACIÓN Y SEGURIDAD ---

it('falla si el stock es insuficiente', function () {
   
    $token = createCustomerToken();
    $category = Category::create(['name' => 'Hardware', 'slug' => 'hardware']);
    $product = Product::create([
        'name' => 'Nvidia RTX',
        'category_id' => $category->id,
        'description' => 'Grafica',
        'price' => 1500,
        'stock' => 1, 
        'slug' => 'nvidia-rtx'
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/orders', [
            'shipping_address' => 'País Vasco',
            'contact_email' => 'dorki@okin.eus',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5]
            ]
        ]);

    $response->assertStatus(400); 
    $response->assertJsonFragment(['status' => false]);
});

it('falla si el carrito viene vacío', function () {
   
    $token = createCustomerToken();

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/orders', [
            'shipping_address' => 'Calle Falsa 123',
            'contact_email' => 'error@test.com',
            'items' => [] 
        ]);

    $response->assertStatus(422);
    $response->assertJsonFragment([
        'items' => ['No puedes realizar un pedido sin productos.']
    ]);
});

it('usa el precio de la base de datos y no el que mande el usuario', function () {
  
    $token = createCustomerToken();
    $category = Category::create(['name' => 'Lujo', 'slug' => 'lujo']);
    $product = Product::create([
        'name' => 'Rascacielos Oro',
        'category_id' => $category->id,
        'description' => 'Caro',
        'price' => 500000.00, 
        'stock' => 5,
        'slug' => 'oro'
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/orders', [
            'shipping_address' => 'País Vasco',
            'contact_email' => 'dorki@okin.eus',
            'items' => [
                [
                    'product_id' => $product->id, 
                    'quantity' => 1,
                    'price' => 1.00 // Intento de fraude
                ]
            ]
        ]);

    $response->assertStatus(201);

    // ADAPTADO: Usamos 'unit_price' para que coincida con tu OrderController
    $this->assertDatabaseHas('order_product', [
        'product_id' => $product->id,
        'unit_price' => 500000.00 
    ]);
});

it('falla si el producto tiene stock cero', function () {
  
    $token = createCustomerToken();
    $category = Category::create(['name' => 'Test', 'slug' => 'test']);
    $product = Product::create([
        'name' => 'Producto Agotado',
        'category_id' => $category->id,
        'description' => 'Sin existencias',
        'price' => 10,
        'stock' => 0, 
        'slug' => 'agotado'
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/orders', [
            'shipping_address' => 'Calle Falsa',
            'contact_email' => 'dorki@okin.eus',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1]
            ]
        ]);

    $response->assertStatus(400);
});

it('prohíbe a un cliente ver todos los pedidos de la tienda', function () {
   
    $user = User::create(['name' => 'Cliente', 'email' => 'c@c.com', 'password' => '1', 'rol' => 'cliente']);
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/admin/all-orders');

    $response->assertStatus(403); // El portero is_admin debe pararle los pies
});

it('permite a un usuario ver solo sus propios pedidos', function () {
    
    
    // 1. Creamos dos usuarios diferentes
    $userA = User::create(['name' => 'User A', 'email' => 'a@a.com', 'password' => '1', 'rol' => 'cliente']);
    $userB = User::create(['name' => 'User B', 'email' => 'b@b.com', 'password' => '1', 'rol' => 'cliente']);
    
    $tokenA = JWTAuth::fromUser($userA);

    // 2. Creamos un pedido para el User A y otro para el User B
    Order::create(['user_id' => $userA->id, 'total' => 100, 'status' => 'pending', 'shipping_address' => 'Dir A', 'contact_email' => 'a@a.com']);
    Order::create(['user_id' => $userB->id, 'total' => 200, 'status' => 'pending', 'shipping_address' => 'Dir B', 'contact_email' => 'b@b.com']);

    // 3. El User A consulta SUS pedidos
    $response = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
        ->getJson('/api/orders');

    $response->assertStatus(200)
             ->assertJsonCount(1, 'data') // Solo debe ver 1 pedido, no 2
             ->assertJsonFragment(['total' => 100]);
});

it('permite al admin ver todos los pedidos de la tienda', function () {
    
    
    // 1. Creamos un Admin y un par de pedidos de gente random
    $admin = User::create(['name' => 'Admin', 'email' => 'admin@okin.eus', 'password' => '1', 'rol' => 'admin']);
    $tokenAdmin = JWTAuth::fromUser($admin);
    
    $user = User::create(['name' => 'C', 'email' => 'c@c.com', 'password' => '1', 'rol' => 'cliente']);
    Order::create(['user_id' => $user->id, 'total' => 50, 'status' => 'pending', 'shipping_address' => 'X', 'contact_email' => 'c@c.com']);
    Order::create(['user_id' => $user->id, 'total' => 75, 'status' => 'pending', 'shipping_address' => 'Y', 'contact_email' => 'c@c.com']);

    // 2. El Admin consulta la ruta especial de administración
    $response = $this->withHeader('Authorization', 'Bearer ' . $tokenAdmin)
        ->getJson('/api/admin/all-orders');

    $response->assertStatus(200)
             ->assertJsonCount(2, 'data'); // El admin ve todo
});

it('bloquea a un cliente que intenta acceder a la ruta de todos los pedidos', function () {
   
    
    $user = User::create(['name' => 'Hacker', 'email' => 'h@h.com', 'password' => '1', 'rol' => 'cliente']);
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/admin/all-orders');

    // El middleware is_admin debería lanzar un 403
    $response->assertStatus(403);
});