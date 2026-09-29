<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('orders/Index', [
            'orders' => Order::with('items.foodItem')->latest('id')->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('orders/Create', [
            'foodItems' => FoodItem::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.food_item_id' => ['required', 'integer', 'distinct', 'exists:food_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Add at least one item to the order.',
        ]);

        // Any exception thrown inside the transaction rolls back the order and all stock changes.
        $order = DB::transaction(function () use ($validated) {
            $order = Order::create(['total' => 0]);
            $total = 0;

            foreach ($validated['items'] as $line) {
                $foodItem = FoodItem::whereKey($line['food_item_id'])->firstOrFail();

                // Only decrement if enough stock is left, as a single UPDATE,
                // so two orders at the same time can't both take the last serving.
                $reserved = FoodItem::whereKey($foodItem->id)
                    ->where('stock', '>=', $line['quantity'])
                    ->decrement('stock', $line['quantity']);

                if (! $reserved) {
                    throw ValidationException::withMessages([
                        'items' => "Not enough {$foodItem->name}. Only {$foodItem->stock} left.",
                    ]);
                }

                $order->items()->create([
                    'food_item_id' => $foodItem->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $foodItem->price,
                ]);

                $total += $foodItem->price * $line['quantity'];
            }

            $order->update(['total' => $total]);

            return $order;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => "Order #{$order->id} placed. Total ₱{$order->total}."]);

        return to_route('orders.index');
    }
}
