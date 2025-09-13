<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    private $realProducts = [
        [
            'name' => 'Mitsubishi Triton 2015-22 Genuine Radiator Reserve Tank Cap 1375A433',
            'brand' => 'Mitsubishi Parts',
            'part_number' => '1375A433',
            'condition' => 'New',
            'quantity' => 42,
            'price' => 33.00,
            'original_price' => null,
            'description' => 'Genuine radiator reserve tank cap for Mitsubishi Triton models from 2015 to 2022. Ensures a perfect fit and reliable performance.',
            'images' => [
                'https://picsum.photos/seed/M0001_1/400/400',
                'https://picsum.photos/seed/M0001_2/400/400',
                'https://picsum.photos/seed/M0001_3/400/400',
                'https://picsum.photos/seed/M0001_4/400/400'
            ]
        ],
        [
            'name' => 'TOYOTA HILUX 2015-24 Genuine Left Mirror, no camera & blind spot, 5 wires BLACK',
            'brand' => 'Toyota Parts',
            'part_number' => '87940-0K360',
            'condition' => 'Used',
            'quantity' => 15,
            'price' => 159.20,
            'original_price' => 199.00,
            'description' => 'Genuine left-side mirror for Toyota Hilux models 2015-2024. Comes in black, without camera and blind spot features, with a 5-wire connector.',
            'images' => [
                'https://picsum.photos/seed/T0001_1/400/400',
                'https://picsum.photos/seed/T0001_2/400/400',
                'https://picsum.photos/seed/T0001_3/400/400',
                'https://picsum.photos/seed/T0001_4/400/400'
            ]
        ],
        [
            'name' => 'TOYOTA HILUX 2015-23 GENUINE OUTER WINDOW SEALS WEATHER STRIP 4 Doors',
            'brand' => 'Toyota Parts',
            'part_number' => '68160-0K010',
            'condition' => 'New',
            'quantity' => 33,
            'price' => 128.00,
            'original_price' => 160.00,
            'description' => 'Complete set of genuine outer window seals for all four doors of the Toyota Hilux 2015-2023. Protects your interior from the elements.',
            'images' => [
                'https://picsum.photos/seed/T0002_1/400/400',
                'https://picsum.photos/seed/T0002_2/400/400',
                'https://picsum.photos/seed/T0002_3/400/400',
                'https://picsum.photos/seed/T0002_4/400/400'
            ]
        ],
        [
            'name' => 'Ford Ranger 2018-22 Headlight Assembly',
            'brand' => 'Ford Parts',
            'part_number' => 'AB39-13005-AD',
            'condition' => 'Used',
            'quantity' => 21,
            'price' => 180.00,
            'original_price' => null,
            'description' => 'Complete headlight assembly for Ford Ranger models from 2018 to 2022. OEM quality for a perfect fit and optimal visibility.',
            'images' => [
                'https://picsum.photos/seed/F0001_1/400/400',
                'https://picsum.photos/seed/F0001_2/400/400',
                'https://picsum.photos/seed/F0001_3/400/400',
                'https://picsum.photos/seed/F0001_4/400/400'
            ],
            'videos' => ['https://www.w3schools.com/html/mov_bbb.mp4']
        ],
        [
            'name' => 'Honda Civic 2020-23 Brake Pad Set',
            'brand' => 'Honda Parts',
            'part_number' => '45022-TBA-A00',
            'condition' => 'New',
            'quantity' => 49,
            'price' => 75.50,
            'original_price' => null,
            'description' => 'Front brake pad set for Honda Civic models from 2020 to 2023. High-performance material for superior stopping power.',
            'images' => [
                'https://picsum.photos/seed/H0001_1/400/400',
                'https://picsum.photos/seed/H0001_2/400/400',
                'https://picsum.photos/seed/H0001_3/400/400',
                'https://picsum.photos/seed/H0001_4/400/400'
            ]
        ],
        [
            'name' => 'BMW 3 Series 2019-2023 All-Weather Floor Mats',
            'brand' => 'BMW Parts',
            'part_number' => '51472458560',
            'condition' => 'New',
            'quantity' => 37,
            'price' => 150.00,
            'original_price' => 175.00,
            'description' => 'Custom-fit all-weather floor mats for BMW 3 Series 2019-2023. Protects your vehicle\'s interior from dirt, spills, and wear.',
            'images' => [
                'https://picsum.photos/seed/B0001_1/400/400',
                'https://picsum.photos/seed/B0001_2/400/400',
                'https://picsum.photos/seed/B0001_3/400/400',
                'https://picsum.photos/seed/B0001_4/400/400'
            ]
        ],
        [
            'name' => 'Volkswagen Golf 2018-21 Alternator',
            'brand' => 'Volkswagen Parts',
            'part_number' => '06L903021J',
            'condition' => 'Used',
            'quantity' => 3,
            'price' => 320.00,
            'original_price' => 350.00,
            'description' => 'High-output alternator for Volkswagen Golf models from 2018 to 2021. Ensures your battery stays charged and electrical systems run smoothly.',
            'images' => [
                'https://picsum.photos/seed/V0001_1/400/400',
                'https://picsum.photos/seed/V0001_2/400/400',
                'https://picsum.photos/seed/V0001_3/400/400',
                'https://picsum.photos/seed/V0001_4/400/400'
            ]
        ],
        [
            'name' => 'Jeep Wrangler 2018-2023 LED Fog Lights',
            'brand' => 'Jeep Parts',
            'part_number' => '82215394',
            'condition' => 'New',
            'quantity' => 2,
            'price' => 120.00,
            'original_price' => null,
            'description' => 'Bright LED fog lights for Jeep Wrangler 2018-2023. Improves visibility in foggy or low-light conditions.',
            'images' => [
                'https://picsum.photos/seed/J0001_1/400/400',
                'https://picsum.photos/seed/J0001_2/400/400',
                'https://picsum.photos/seed/J0001_3/400/400',
                'https://picsum.photos/seed/J0001_4/400/400'
            ]
        ],
        [
            'name' => 'Porsche 911 2022-2024 Indoor Car Cover',
            'brand' => 'Porsche Parts',
            'part_number' => '99204400000',
            'condition' => 'New',
            'quantity' => 1,
            'price' => 450.00,
            'original_price' => null,
            'description' => 'Custom-fit indoor car cover for Porsche 911 2022-2024. Protects your vehicle from dust, dirt, and scratches.',
            'images' => [
                'https://picsum.photos/seed/P0001_1/400/400',
                'https://picsum.photos/seed/P0001_2/400/400',
                'https://picsum.photos/seed/P0001_3/400/400',
                'https://picsum.photos/seed/P0001_4/400/400'
            ]
        ]
    ];

    private $brands = [
        'Mitsubishi Parts', 'Toyota Parts', 'Ford Parts', 'Honda Parts', 'Mazda Parts',
        'Nissan Parts', 'Subaru Parts', 'Volkswagen Parts', 'Audi Parts', 'BMW Parts',
        'Chevrolet Parts', 'Jeep Parts', 'Kia Parts', 'Lexus Parts', 'Porsche Parts',
        'Ram Parts', 'GMC Parts', 'Isuzu Parts'
    ];

    private $categories = [
        'Engine Parts', 'Brake Parts', 'Electrical', 'Body Parts', 'Suspension', 
        'Transmission', 'Mirrors', 'Lighting', 'Interior', 'Exterior', 'Filters',
        'Cooling System'
    ];

    private $conditions = ['New', 'Used', 'Refurbished'];

    public function definition()
    {
        // 60% chance to use real product data, 40% chance to generate fake data
        if ($this->faker->boolean(60)) {
            $product = $this->faker->randomElement($this->realProducts);
            return $this->buildRealProduct($product);
        } else {
            return $this->buildFakeProduct();
        }
    }

    private function buildRealProduct($product)
    {
        return [
            'name' => $product['name'],
            'brand' => $product['brand'],
            'category' => $this->getCategory($product['name']),
            'part_number' => $product['part_number'] . '-' . $this->faker->unique()->numberBetween(100, 999),
            'condition' => $product['condition'],
            'quantity' => $product['quantity'],
            'original_price' => $product['original_price'],
            'price' => $product['price'],
            'description' => $product['description'],
            'images' => $product['images'],
            'videos' => $product['videos'] ?? null,
            'is_active' => $this->faker->boolean(90),
        ];
    }

    private function buildFakeProduct()
    {
        $brand = $this->faker->randomElement($this->brands);
        $originalPrice = $this->faker->randomFloat(2, 20, 1000);
        $hasDiscount = $this->faker->boolean(40);

        return [
            'name' => $this->generateProductName($brand),
            'brand' => $brand,
            'category' => $this->faker->randomElement($this->categories),
            'part_number' => $this->generatePartNumber(),
            'condition' => $this->faker->randomElement($this->conditions),
            'quantity' => $this->faker->numberBetween(0, 50),
            'original_price' => $hasDiscount ? $originalPrice : ($this->faker->boolean(20) ? $originalPrice : null),
            'price' => $hasDiscount ? $this->faker->randomFloat(2, 10, $originalPrice * 0.9) : $originalPrice,
            'description' => $this->generateDescription(),
            'images' => $this->generateImages(),
            'videos' => $this->faker->boolean(20) ? [$this->generateVideo()] : null,
            'is_active' => $this->faker->boolean(85),
        ];
    }

    private function getCategory($productName)
    {
        $name = strtolower($productName);
        
        if (str_contains($name, 'mirror')) return 'Mirrors';
        if (str_contains($name, 'headlight') || str_contains($name, 'fog light')) return 'Lighting';
        if (str_contains($name, 'brake')) return 'Brake Parts';
        if (str_contains($name, 'alternator')) return 'Electrical';
        if (str_contains($name, 'filter')) return 'Filters';
        if (str_contains($name, 'radiator')) return 'Cooling System';
        if (str_contains($name, 'floor mat') || str_contains($name, 'door sill')) return 'Interior';
        if (str_contains($name, 'cover') || str_contains($name, 'protector')) return 'Exterior';
        if (str_contains($name, 'window seal')) return 'Body Parts';
        
        return $this->faker->randomElement($this->categories);
    }

    private function generateProductName($brand)
    {
        $brandName = str_replace(' Parts', '', $brand);
        $models = [
            'Civic', 'Accord', 'CR-V', 'Pilot', 'Fit',
            'Camry', 'Corolla', 'RAV4', 'Highlander', 'Prius',
            'F-150', 'Mustang', 'Explorer', 'Escape', 'Focus',
            '3 Series', 'X3', 'X5', 'A4', 'Q5'
        ];
        
        $parts = [
            'Brake Pad Set', 'Oil Filter', 'Air Filter', 'Spark Plugs',
            'Headlight Assembly', 'Tail Light', 'Mirror Assembly',
            'Floor Mats', 'Seat Covers', 'Radiator', 'Alternator'
        ];
        
        $model = $this->faker->randomElement($models);
        $part = $this->faker->randomElement($parts);
        $year = $this->faker->numberBetween(2015, 2024);
        
        return "{$brandName} {$model} {$year} {$part}";
    }

    private function generatePartNumber()
    {
        $patterns = [
            '######-##-###',
            '##-####-###',
            '########',
            '###-###-####'
        ];
        
        return strtoupper($this->faker->bothify($this->faker->randomElement($patterns)));
    }

    private function generateDescription()
    {
        $descriptions = [
            'High-quality OEM replacement part designed for optimal performance and durability.',
            'Genuine part that ensures perfect fit and reliable operation for your vehicle.',
            'Premium aftermarket component engineered to meet or exceed factory specifications.',
            'Direct replacement part that provides excellent value and long-lasting performance.',
            'Professional-grade component suitable for both repair and maintenance applications.'
        ];
        
        return $this->faker->randomElement($descriptions);
    }

    private function generateImages()
    {
        $seed = $this->faker->unique()->word;
        return [
            "https://picsum.photos/seed/{$seed}_1/400/400",
            "https://picsum.photos/seed/{$seed}_2/400/400",
            "https://picsum.photos/seed/{$seed}_3/400/400",
            "https://picsum.photos/seed/{$seed}_4/400/400"
        ];
    }

    private function generateVideo()
    {
        $videos = [
            'https://www.w3schools.com/html/mov_bbb.mp4',
            'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4'
        ];
        
        return $this->faker->randomElement($videos);
    }

    // State methods for specific scenarios
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
        return $this->state(['original_price' => null]);
    }

    public function realProduct()
    {
        return $this->state(function (array $attributes) {
            $product = $this->faker->randomElement($this->realProducts);
            return $this->buildRealProduct($product);
        });
    }

    public function fakeProduct()
    {
        return $this->state(function (array $attributes) {
            return $this->buildFakeProduct();
        });
    }
}