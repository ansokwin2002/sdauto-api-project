<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
// Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Product API routes
    Route::prefix('products')->group(function () {
        // Statistics route (before parameterized routes)
        Route::get('/stats', [ProductController::class, 'getStats']);
        
        // Filter routes
        Route::get('/brands', [ProductController::class, 'getBrands']);
        Route::get('/categories', [ProductController::class, 'getCategories']);
        Route::get('/on-sale', [ProductController::class, 'getOnSaleProducts']);
        
        // Bulk operations
        Route::post('/bulk', [ProductController::class, 'bulkOperation']);
        
        // Main CRUD routes
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{product}', [ProductController::class, 'show'])->missing(function (Request $request) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        });
        Route::put('/{product}', [ProductController::class, 'update'])->missing(function (Request $request) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        });
        Route::patch('/{product}', [ProductController::class, 'update'])->missing(function (Request $request) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        });
        Route::delete('/{product}', [ProductController::class, 'destroy'])->missing(function (Request $request) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        });
        
        // Stock and pricing management
        Route::patch('/{product}/stock', [ProductController::class, 'updateStock']);
        Route::patch('/{product}/discount', [ProductController::class, 'applyDiscount']);
    });
// });