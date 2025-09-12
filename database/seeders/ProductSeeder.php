<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Create products with various states
        Product::factory(20)->active()->inStock()->onSale()->create();      // Products on sale
        Product::factory(15)->active()->inStock()->noDiscount()->create();   // Regular products
        Product::factory(8)->active()->outOfStock()->create();               // Out of stock
        Product::factory(5)->inactive()->create();                           // Inactive products
        Product::factory(2)->active()->create();                             // Random quantities and pricing
        Product::factory()->count(100)->create();
    }
}