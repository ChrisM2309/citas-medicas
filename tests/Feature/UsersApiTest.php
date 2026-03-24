<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('el listado de usuarios requiere autenticacion', function () {
    // El modulo de usuarios esta bajo middleware auth:sanctum.
    $this->getJson('/api/v1/users')
        ->assertUnauthorized();
});

test('el listado de usuarios devuelve una coleccion resource', function () {
    // Confirmamos la estructura JSON esperada por el frontend.
    authenticateWithPermissions(['manage_users']);
    $userA = User::factory()->create(['is_active' => true]);
    $userB = User::factory()->create(['is_active' => true]);

    $response = $this->getJson('/api/v1/users')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
    expect(collect($response->json('data'))->pluck('id')->all())->toContain($userA->id, $userB->id);
});

test('se puede crear un usuario y la contrasena queda hasheada', function () {
    // Este caso protege un detalle de seguridad muy importante.
    authenticateWithPermissions(['manage_users']);

    $response = $this->postJson('/api/v1/users', [
        'name' => 'Carlos',
        'email' => 'carlos@example.com',
        'password' => 'password123',
        'is_active' => true,
    ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'carlos@example.com');

    $user = User::query()->where('email', 'carlos@example.com')->firstOrFail();

    expect(Hash::check('password123', $user->password))->toBeTrue();
    expect($response->json('data.id'))->toBe($user->id);
});

test('no se puede crear un usuario con correo repetido', function () {
    // Protegemos la unicidad del login.
    authenticateWithPermissions(['manage_users']);
    User::factory()->create(['email' => 'carlos@example.com']);

    $this->postJson('/api/v1/users', [
        'name' => 'Carlos',
        'email' => 'carlos@example.com',
        'password' => 'password123',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('se puede consultar el propio detalle de usuario autenticado', function () {
    // La policy permite ver tu propio perfil aunque no administres usuarios.
    $user = authenticateWithPermissions();

    $this->getJson("/api/v1/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

test('se puede actualizar un usuario incluyendo su estado activo', function () {
    // Cubrimos un cambio comun de administracion.
    authenticateWithPermissions(['manage_users']);
    $user = User::factory()->create(['is_active' => true]);

    $this->putJson("/api/v1/users/{$user->id}", [
        'name' => 'Usuario editado',
        'is_active' => false,
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Usuario editado')
        ->assertJsonPath('data.is_active', false);
});

test('se puede eliminar un usuario autenticado', function () {
    // El controlador responde con mensaje simple al borrar un usuario.
    authenticateWithPermissions(['manage_users']);
    $user = User::factory()->create();

    $this->deleteJson("/api/v1/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Usuario eliminado correctamente');

    $this->assertSoftDeleted('users', ['id' => $user->id]);
});
