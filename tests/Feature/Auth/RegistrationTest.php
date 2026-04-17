<?php

use Database\Seeders\RoleSeeder;
use Laravel\Fortify\Features;

beforeEach(function (): void {
    $this->skipUnlessFortifyHas(Features::registration());
    $this->seed(RoleSeeder::class);
});

test('registration screen can be rendered', function (): void {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function (): void {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
