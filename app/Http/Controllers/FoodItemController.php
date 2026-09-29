<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FoodItemController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('food-items/Index', [
            'foodItems' => FoodItem::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        FoodItem::create($request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:food_items,name'],
            'price' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
        ]));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Food item added.']);

        return to_route('food-items.index');
    }

    public function update(Request $request, FoodItem $foodItem): RedirectResponse
    {
        $foodItem->update($request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('food_items')->ignore($foodItem)],
            'price' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
        ]));

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$foodItem->name} updated."]);

        return to_route('food-items.index');
    }
}
