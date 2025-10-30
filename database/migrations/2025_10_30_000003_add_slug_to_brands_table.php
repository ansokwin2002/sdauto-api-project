<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Brand;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->string('slug')->unique()->after('brand_name');
        });

        // Generate slugs for existing brands
        $this->generateSlugsForExistingBrands();
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }

    private function generateSlugsForExistingBrands(): void
    {
        $brands = Brand::all();
        
        foreach ($brands as $brand) {
            $slug = Str::slug($brand->brand_name);
            $originalSlug = $slug;
            $counter = 1;
            
            // Ensure slug is unique
            while (Brand::where('slug', $slug)->where('id', '!=', $brand->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $brand->update(['slug' => $slug]);
        }
    }
};
