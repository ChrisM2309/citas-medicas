<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

test('el login devuelve token cuando las credenciales son correctas', function () {
    // Verificamos el camino feliz del login para asegurar que la API emite tokens.
    $user = User::factory()->create([
        'email' => 'ana@example.com',
        'password' => 'password123',
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Login exitoso')
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.email', 'ana@example.com');

    expect($response->json('access_token'))->not->toBeEmpty();
    expect(PersonalAccessToken::query()->count())->toBe(1);
});

test('el login falla cuando la contrasena es incorrecta', function () {
    // Protegemos la autenticacion ante intentos con password invalida.
    $user = User::factory()->create([
        'password' => 'password123',
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'otra-clave',
    ])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Credenciales invalidas');
});

test('el login falla cuando el usuario esta inactivo', function () {
    // Confirmamos que un usuario deshabilitado no puede autenticarse aunque conozca su clave.
    $user = User::factory()->create([
        'password' => 'password123',
        'is_active' => false,
    ]);

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'El usuario esta inactivo');
});

test('el login valida que el correo sea obligatorio', function () {
    // Cubrimos la validacion base del request de autenticacion.
    $this->postJson('/api/v1/login', [
        'password' => 'password123',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('el endpoint me devuelve el usuario autenticado', function () {
    // Aseguramos que el frontend pueda consultar la sesion actual.
    $user = authenticateWithPermissions();

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

test('el logout elimina el token actual', function () {
    // Verificamos que cerrar sesion invalide el token usado en la peticion.
    $user = User::factory()->create([
        'is_active' => true,
    ]);
    $token = $user->createToken('prueba');

    $this->withToken($token->plainTextToken)
        ->postJson('/api/v1/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logout exitoso');

    expect(PersonalAccessToken::query()->count())->toBe(0);
});

test('los endpoints protegidos rechazan usuarios no autenticados', function () {
    // Protegemos el perimetro minimo de la API.
    $this->getJson('/api/v1/patients')
        ->assertUnauthorized();
});
