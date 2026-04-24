<?php

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Models\User;
use Illuminate\Support\Facades\Event;
use App\Events\UserRegistered;
use App\Events\UserLoggedIn;

it('permite a un usuario registrarse correctamente', function () {
    
    Event::fake(); // Para no disparar eventos reales

    $response = $this->postJson('/api/register', [
        'name'     => 'Dorki',
        'email'    => 'dorki@okin.eus',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('users', [
        'email' => 'dorki@okin.eus'
    ]);

    Event::assertDispatched(UserRegistered::class);
});

it('devuelve un token cuando el login es correcto', function () {
    
    Event::fake();

    User::create([
        'name'     => 'Dorki Login',
        'email'    => 'login@test.com',
        'password' => bcrypt('secret123'),
        'rol'      => 'cliente'
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'login@test.com',
        'password' => 'secret123',
    ]);

    // ADAPTADO: Buscamos 'token', no 'access_token'
    $response->assertStatus(200)
             ->assertJsonStructure([
                 'token', 
                 'token_type',
                 'expires_in'
             ]);

    Event::assertDispatched(UserLoggedIn::class);
});

it('bloquea el acceso a rutas protegidas sin token', function () {
    
    $response = $this->getJson('/api/who');
    $response->assertStatus(401);
});


it('no permite el login con contraseña incorrecta', function () {
    /** @var \Tests\TestCase $this */
    $user = User::create([
        'name' => 'Dorki Despistado', 
        'email' => 'error@okin.eus', 
        'password' => bcrypt('correcta123'), 
        'rol' => 'cliente'
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'error@okin.eus', 
        'password' => 'incorrecta999' // <--- Contraseña mala
    ]);

    // Debería dar error 401 Unauthorized
    $response->assertStatus(401)
             ->assertJson(['error' => 'Usuario o contraseña inválida']);
});

it('devuelve los datos del perfil (who) con un token válido', function () {
    /** @var \Tests\TestCase $this */
    $user = User::create([
        'name' => 'Dorki Perfil', 
        'email' => 'who@test.com', 
        'password' => bcrypt('12345678'), 
        'rol' => 'cliente'
    ]);
    
    $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                    ->getJson('/api/who');

    $response->assertStatus(200)
             ->assertJsonFragment(['email' => 'who@test.com', 'name' => 'Dorki Perfil']);
});

it('cierra la sesión (logout) e invalida el token', function () {
    /** @var \Tests\TestCase $this */
    $user = User::create([
        'name' => 'Dorki Salida', 
        'email' => 'bye@test.com', 
        'password' => bcrypt('12345678'), 
        'rol' => 'cliente'
    ]);
    
    $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

    // 1. Hacemos logout
    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                    ->postJson('/api/logout');
                    
    $response->assertStatus(200)
             ->assertJson(['message' => 'Sesión cerrada correctamente']);

    // 2. Intentamos entrar a una ruta protegida con el token que acabamos de matar
    $responseFail = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->getJson('/api/who');
                         
    // El middleware debe cazarnos y devolver 401
    $responseFail->assertStatus(401);
});

it('refresca el token correctamente y bloquea el antiguo', function () {
    /** @var \Tests\TestCase $this */
    $user = User::create([
        'name' => 'Dorki Refresh', 
        'email' => 'ref@test.com', 
        'password' => bcrypt('12345678'), 
        'rol' => 'cliente'
    ]);
    
    $oldToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

    // 1. Pedimos un token nuevo usando el viejo
    $response = $this->withHeader('Authorization', 'Bearer ' . $oldToken)
                    ->postJson('/api/refresh');
                    
    $response->assertStatus(200)
             ->assertJsonStructure(['token', 'token_type', 'expires_in']);

    $newToken = $response->json('token');

    // 2. Comprobamos que el token VIEJO ya no sirve
    $this->withHeader('Authorization', 'Bearer ' . $oldToken)
         ->getJson('/api/who')
         ->assertStatus(401);

    // 3. Comprobamos que el token NUEVO sí funciona
    $this->withHeader('Authorization', 'Bearer ' . $newToken)
         ->getJson('/api/who')
         ->assertStatus(200);
});

it('falla al registrar un usuario si el email ya existe', function () {
    /** @var \Tests\TestCase $this */
    
    // 1. Creamos un usuario primero
    User::create([
        'name' => 'Original',
        'email' => 'duplicado@okin.eus',
        'password' => bcrypt('12345678'),
        'rol' => 'cliente'
    ]);

    // 2. Intentamos registrar a otro con el mismo email
    $response = $this->postJson('/api/register', [
        'name'     => 'Copia',
        'email'    => 'duplicado@okin.eus', 
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);

    // 3. ADAPTADO: Buscamos dentro de 'errores' y le quitamos el punto final
    $response->assertJson([
        'errores' => [
            'email' => ['Este correo electrónico ya está registrado']
        ]
    ]);
});

it('bloquea el acceso si el token es falso o inventado', function () {
    /** @var \Tests\TestCase $this */
    
    $tokenFalso = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.falso.inventado';

    $response = $this->withHeader('Authorization', 'Bearer ' . $tokenFalso)
                     ->getJson('/api/who');

    $response->assertStatus(401);
});

