<?php

use App\Models\User;

test('home redirects guests to the login page', function () {
    $this->get(route('home'))->assertRedirect('/login');
});

test('home sends logged in users to their orders', function () {
    $this->actingAs(User::factory()->create());

    $this->followingRedirects()
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('orders/Index'));
});
