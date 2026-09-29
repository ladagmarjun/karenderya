<?php

namespace Database\Seeders;

use App\Models\FoodItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        FoodItem::create(['name' => 'Adobo', 'price' => 70, 'stock' => 10]);
        FoodItem::create(['name' => 'Sinigang', 'price' => 85, 'stock' => 8]);
        FoodItem::create(['name' => 'Lumpia', 'price' => 30, 'stock' => 0]);
        FoodItem::create(['name' => 'Rice', 'price' => 15, 'stock' => 999999]); // syempre bawal maubusan
    }
}
