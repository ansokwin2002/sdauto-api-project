<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Brand;
use App\Models\Product;

return new class extends Migration {
    public function up(): void
    {
        // First, add the brand_id column
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable()->after('brand');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
        });

        // Migrate existing brand data
        $this->migrateBrandData();

        // After migration, we can make brand_id required and remove the old brand column
        // Note: Uncomment these lines after running the migration and verifying data
        /*
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable(false)->change();
            $table->dropColumn('brand');
        });
        */
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });
    }

    private function migrateBrandData(): void
    {
        // Get all unique brand names from products
        $uniqueBrands = Product::whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand');

        // Create brand records for each unique brand
        foreach ($uniqueBrands as $brandName) {
            $brand = Brand::firstOrCreate(['brand_name' => $brandName]);
            
            // Update all products with this brand name to use the brand_id
            Product::where('brand', $brandName)->update(['brand_id' => $brand->id]);
        }

        // Handle products with null or empty brand
        Product::whereNull('brand')
            ->orWhere('brand', '')
            ->update(['brand_id' => null]);
    }
};
