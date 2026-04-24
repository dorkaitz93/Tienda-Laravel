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