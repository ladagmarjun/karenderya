<?php

use App\Models\FoodItem;
use App\Models\Order;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    $this->adobo = FoodItem::create(['name' => 'Adobo', 'price' => 70, 'stock' => 10]);
    $this->rice = FoodItem::create(['name' => 'Rice', 'price' => 15, 'stock' => 999999]);
});

function placeOrder(array $items)
{
    return test()->post(route('orders.store'), [
        'items' => collect($items)
            ->map(fn ($quantity, $id) => ['food_item_id' => $id, 'quantity' => $quantity])
            ->values()
            ->all(),
    ]);
}

test('an order is saved with its items and the total is computed on the server', function () {
    placeOrder([$this->adobo->id => 1, $this->rice->id => 3])
        ->assertRedirect(route('orders.index'));

    $order = Order::with('items')->sole();

    expect($order->total)->toBe(115)
        ->and($order->items)->toHaveCount(2)
        ->and($order->items->firstWhere('food_item_id', $this->rice->id)->quantity)->toBe(3);
});

test('placing an order reduces stock', function () {
    placeOrder([$this->adobo->id => 4]);

    expect($this->adobo->fresh()->stock)->toBe(6);
});

test('an order is rejected when there is not enough stock', function () {
    placeOrder([$this->adobo->id => 11])
        ->assertSessionHasErrors(['items' => 'Not enough Adobo. Only 10 left.']);

    expect(Order::count())->toBe(0)
        ->and($this->adobo->fresh()->stock)->toBe(10);
});

test('a failed line rolls back the whole order, including stock already taken', function () {
    // Rice is valid and processed first, then Adobo fails.
    $this->post(route('orders.store'), ['items' => [
        ['food_item_id' => $this->rice->id, 'quantity' => 2],
        ['food_item_id' => $this->adobo->id, 'quantity' => 50],
    ]])->assertSessionHasErrors('items');

    expect(Order::count())->toBe(0)
        ->and($this->rice->fresh()->stock)->toBe(999999);
});

test('the last servings can be ordered, then the item is sold out', function () {
    placeOrder([$this->adobo->id => 10])->assertSessionHasNoErrors();
    placeOrder([$this->adobo->id => 1])->assertSessionHasErrors('items');

    expect($this->adobo->fresh()->stock)->toBe(0)
        ->and(Order::count())->toBe(1);
});

test('an empty order is rejected', function () {
    $this->post(route('orders.store'), ['items' => []])
        ->assertSessionHasErrors('items');
});

test('quantities must be positive and items must exist', function () {
    $this->post(route('orders.store'), ['items' => [
        ['food_item_id' => $this->adobo->id, 'quantity' => 0],
        ['food_item_id' => 9999, 'quantity' => 1],
    ]])->assertSessionHasErrors(['items.0.quantity', 'items.1.food_item_id']);
});

test('old orders keep the price they were placed at', function () {
    placeOrder([$this->adobo->id => 2]);

    $this->adobo->update(['price' => 100]);

    $order = Order::with('items')->sole();
    expect($order->total)->toBe(140)
        ->and($order->items->first()->unit_price)->toBe(70);
});

test('orders page lists previous orders newest first', function () {
    placeOrder([$this->adobo->id => 1]);
    placeOrder([$this->rice->id => 2]);

    $this->get(route('orders.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('orders/Index')
            ->has('orders.data', 2)
            ->where('orders.data.0.total', 30)
            ->where('orders.data.0.items.0.food_item.name', 'Rice')
            ->where('orders.data.1.total', 70));
});

test('orders are paginated 10 per page', function () {
    foreach (range(1, 12) as $i) {
        placeOrder([$this->rice->id => $i]);
    }

    $this->get(route('orders.index'))
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 10)
            ->where('orders.data.0.total', 12 * 15) // newest first
            ->where('orders.last_page', 2));

    $this->get(route('orders.index', ['page' => 2]))
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 2)
            ->where('orders.data.1.total', 15)); // the very first order
});

test('new order page shows the menu', function () {
    $this->get(route('orders.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('orders/Create')
            ->has('foodItems', 2));
});
