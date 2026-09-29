<?php

use App\Http\Controllers\FoodItemController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/orders')->name('dashboard');

    Route::resource('food-items', FoodItemController::class)->only(['index', 'store', 'update']);
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store']);
});

require __DIR__.'/settings.php';
