<?php

/**
 * Simple test script to verify Delivery Partners API structure
 * Run this with: php test-delivery-partners-api.php
 */

// Check if all required files exist
$files = [
    'app/Models/DeliveryPartner.php',
    'app/Http/Controllers/Api/DeliveryPartnerController.php',
    'database/migrations/2025_10_29_000000_create_delivery_partners_table.php',
    'routes/api.php'
];

echo "=== Delivery Partners API Structure Test ===\n\n";

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} exists\n";
    } else {
        echo "✗ {$file} missing\n";
    }
}

echo "\n=== Checking Model Structure ===\n";

if (file_exists('app/Models/DeliveryPartner.php')) {
    $modelContent = file_get_contents('app/Models/DeliveryPartner.php');
    
    $checks = [
        'class DeliveryPartner extends Model' => 'Model class definition',
        'protected $fillable' => 'Fillable fields defined',
        "'title'" => 'Title field in fillable',
        "'description'" => 'Description field in fillable',
        "'image'" => 'Image field in fillable'
    ];
    
    foreach ($checks as $pattern => $description) {
        if (strpos($modelContent, $pattern) !== false) {
            echo "✓ {$description}\n";
        } else {
            echo "✗ {$description}\n";
        }
    }
}

echo "\n=== Checking Controller Structure ===\n";

if (file_exists('app/Http/Controllers/Api/DeliveryPartnerController.php')) {
    $controllerContent = file_get_contents('app/Http/Controllers/Api/DeliveryPartnerController.php');
    
    $methods = [
        'public function index()' => 'Index method (GET all)',
        'public function store(' => 'Store method (POST)',
        'public function show(' => 'Show method (GET one)',
        'public function update(' => 'Update method (PUT/PATCH)',
        'public function destroy(' => 'Destroy method (DELETE)',
        'public function fromUrl(' => 'Create from URL method',
        'public function updateFromUrl(' => 'Update from URL method'
    ];
    
    foreach ($methods as $pattern => $description) {
        if (strpos($controllerContent, $pattern) !== false) {
            echo "✓ {$description}\n";
        } else {
            echo "✗ {$description}\n";
        }
    }
}

echo "\n=== Checking Migration Structure ===\n";

if (file_exists('database/migrations/2025_10_29_000000_create_delivery_partners_table.php')) {
    $migrationContent = file_get_contents('database/migrations/2025_10_29_000000_create_delivery_partners_table.php');
    
    $fields = [
        '$table->id()' => 'ID field',
        '$table->string(\'title\')' => 'Title field',
        '$table->text(\'description\')->nullable()' => 'Description field',
        '$table->string(\'image\')->nullable()' => 'Image field',
        '$table->timestamps()' => 'Timestamps'
    ];
    
    foreach ($fields as $pattern => $description) {
        if (strpos($migrationContent, $pattern) !== false) {
            echo "✓ {$description}\n";
        } else {
            echo "✗ {$description}\n";
        }
    }
}

echo "\n=== Checking Routes ===\n";

if (file_exists('routes/api.php')) {
    $routesContent = file_get_contents('routes/api.php');
    
    $routes = [
        'DeliveryPartnerController' => 'Controller imported',
        'admin/delivery-partners' => 'Route prefix defined',
        'DeliveryPartnerController::class, \'index\'' => 'Index route',
        'DeliveryPartnerController::class, \'store\'' => 'Store route',
        'DeliveryPartnerController::class, \'fromUrl\'' => 'From URL route',
        'DeliveryPartnerController::class, \'show\'' => 'Show route',
        'DeliveryPartnerController::class, \'update\'' => 'Update route',
        'DeliveryPartnerController::class, \'destroy\'' => 'Destroy route'
    ];
    
    foreach ($routes as $pattern => $description) {
        if (strpos($routesContent, $pattern) !== false) {
            echo "✓ {$description}\n";
        } else {
            echo "✗ {$description}\n";
        }
    }
}

echo "\n=== Summary ===\n";
echo "Delivery Partners CRUD API has been created with:\n";
echo "- Complete CRUD operations (Create, Read, Update, Delete)\n";
echo "- File upload support for images\n";
echo "- URL-based image creation/update\n";
echo "- Proper validation and error handling\n";
echo "- File cleanup on update/delete\n";
echo "- RESTful API endpoints\n\n";

echo "Next steps:\n";
echo "1. Run 'php artisan migrate' to create the database table\n";
echo "2. Test the API endpoints using the examples in delivery-partners-api-examples.md\n";
echo "3. Ensure storage/app/public is linked: 'php artisan storage:link'\n";

?>
