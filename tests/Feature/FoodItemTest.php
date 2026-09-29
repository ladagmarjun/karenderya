<?php

use App\Models\FoodItem;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('guests cannot see the menu', function () {
    auth()->logout();

    $this->get(route('food-items.index'))->assertRedirect(route('login'));
});

test('menu page lists food items', function () {
    FoodItem::create(['name' => 'Adobo', 'price' => 70, 'stock' => 10]);

    $this->get(route('food-items.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('food-items/Index')
            ->has('foodItems', 1)
            ->where('foodItems.0.name', 'Adobo'));
});

test('a food item can be created', function () {
    $this->post(route('food-items.store'), ['name' => 'Adobo', 'price' => 70, 'stock' => 10])
        ->assertRedirect(route('food-items.index'));

    expect(FoodItem::where('name', 'Adobo')->first())
        ->price->toBe(70)
        ->stock->toBe(10);
});

test('food item names must be unique', function () {
    FoodItem::create(['name' => 'Adobo', 'price' => 70, 'stock' => 10]);

    $this->post(route('food-items.store'), ['name' => 'Adobo', 'price' => 80, 'stock' => 5])
        ->assertSessionHasErrors('name');
});

test('price and stock are validated', function () {
    $this->post(route('food-items.store'), ['name' => 'Adobo', 'price' => 0, 'stock' => -1])
        ->assertSessionHasErrors(['price', 'stock']);

    expect(FoodItem::count())->toBe(0);
});

test('a food item price and stock can be updated', function () {
    $adobo = FoodItem::create(['name' => 'Adobo', 'price' => 70, 'stock' => 10]);

    $this->put(route('food-items.update', $adobo), ['name' => 'Adobo', 'price' => 75, 'stock' => 3])
        ->assertRedirect(route('food-items.index'));

    expect($adobo->fresh())
        ->price->toBe(75)
        ->stock->toBe(3);
});
