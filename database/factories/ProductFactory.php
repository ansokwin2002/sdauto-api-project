<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $brands = ['Ford', 'Toyota', 'Honda', 'BMW', 'Mercedes', 'Audi', 'Chevrolet', 'Nissan'];
        $categories = ['Engine Parts', 'Brake Parts', 'Electrical', 'Body Parts', 'Suspension', 'Transmission'];
        $conditions = ['New', 'Used', 'Refurbished'];
        
        $originalPrice = $this->faker->randomFloat(2, 20, 1000);
        $hasDiscount = $this->faker->boolean(40); // 40% chance of having a discount
        
        return [
            'name' => $this->faker->words(3, true),
            'brand' => $this->faker->randomElement($brands),
            'category' => $this->faker->randomElement($categories),
            'part_number' => strtoupper($this->faker->unique()->bothify('###-???-####')),
            'condition' => $this->faker->randomElement($conditions),
            'quantity' => $this->faker->numberBetween(0, 100),
            'original_price' => $hasDiscount ? $originalPrice : ($this->faker->boolean(20) ? $originalPrice : null),
            'price' => $hasDiscount ? $this->faker->randomFloat(2, 10, $originalPrice * 0.9) : $originalPrice,
            'description' => $this->faker->sentence(10),
            'images' => [
                $this->faker->imageUrl(800, 600, 'auto parts'),
                $this->faker->imageUrl(800, 600, 'auto parts')
            ],
            'videos' => $this->faker->boolean(30) ? [
                'https://www.youtube.com/watch?v=' . $this->faker->regexify('[A-Za-z0-9_-]{11}'),
            ] : null,
            'is_active' => $this->faker->boolean(85),
        ];
    }

    public function active()
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive()
    {
        return $this->state(['is_active' => false]);
    }

    public function inStock()
    {
        return $this->state(['quantity' => $this->faker->numberBetween(1, 100)]);
    }

    public function outOfStock()
    {
        return $this->state(['quantity' => 0]);
    }

    public function onSale()
    {
        return $this->state(function (array $attributes) {
            $originalPrice = $this->faker->randomFloat(2, 50, 500);
            return [
                'original_price' => $originalPrice,
                'price' => $this->faker->randomFloat(2, 25, $originalPrice * 0.8),
            ];
        });
    }

    public function noDiscount()
    {
        return $this->state([
            'original_price' => null
        ]);
    }
}
