<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_brands', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->index();
            $table->string('slug')->unique();
            $table->unsignedInteger('ordering')->default(0)->index();
            $table->timestamps();
            
            // Add index for ordering queries
            $table->index(['ordering', 'brand']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_brands');
    }
};
