<?php

use App\Models\User;

it('redirect to login if not logged in', function () {
    $this->get('/admin')
        ->assertRedirectToRoute('filament.auth.login');
});

it('show dashboard page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/admin')
        ->assertSee('Dashboard')
        ->assertSee("Welcome, $user->name");
});
