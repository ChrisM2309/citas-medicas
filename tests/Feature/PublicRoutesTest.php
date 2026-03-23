<?php

test('la pagina de bienvenida responde correctamente', function () {
    // Mantenemos cubierta la unica ruta web publica.
    $this->get('/')
        ->assertOk();
});

test('el endpoint de login esta disponible sin autenticacion previa', function () {
    // La ruta debe existir incluso cuando la validacion del payload falle.
    $this->postJson('/api/v1/login', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});
