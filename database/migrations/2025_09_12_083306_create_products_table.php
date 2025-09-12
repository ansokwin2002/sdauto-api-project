<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand');
            $table->string('category')->nullable();
            $table->string('part_number')->unique();
            $table->enum('condition', ['New', 'Used', 'Refurbished'])->default('New');
            $table->integer('quantity')->default(0);
            $table->decimal('original_price', 10, 2)->nullable(); // Added original_price (optional)
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->json('videos')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Add indexes for better performance
            $table->index(['brand', 'is_active']);
            $table->index(['category', 'is_active']);
            $table->index(['condition', 'is_active']);
            $table->index('price');
            $table->index('original_price');
            $table->index('quantity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
