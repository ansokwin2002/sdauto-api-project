<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Disable FK checks
        DB::table('products')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Enable FK checks

        // Create products with various states
        Product::factory()->count(100)->create();
    }
}