<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SliderController;

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
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // basic rate limit

// Fallback for unauthenticated access when middleware redirects to 'login' route name
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthorized.'], 401);
})->name('login');

// Protected routes with Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });

    // Home Settings API routes (CRUD)
    Route::prefix('home-settings')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::post('/', [SettingController::class, 'store']);
        Route::get('/{id}', [SettingController::class, 'show']);
        Route::put('/{id}', [SettingController::class, 'update']);
        Route::patch('/{id}', [SettingController::class, 'update']);
    });

    // Slider API routes (CRUD, file upload)
    Route::prefix('sliders')->group(function () {
        Route::get('/', [SliderController::class, 'index']);
        // multipart upload (field: image)
        Route::post('/', [SliderController::class, 'store']);
        Route::post('/upload', [SliderController::class, 'store']);
        // create from remote image URL
        Route::post('/url', [SliderController::class, 'fromUrl']);

        Route::get('/{id}', [SliderController::class, 'show']);
        Route::put('/{id}', [SliderController::class, 'update']);
        Route::patch('/{id}', [SliderController::class, 'update']);
        Route::patch('/{id}/ordering', [SliderController::class, 'updateOrdering']);
        Route::delete('/{id}', [SliderController::class, 'destroy']);
    });

    // Product API routes
    Route::prefix('products')->group(function () {
        Route::get('/stats', [ProductController::class, 'getStats']);
        Route::get('/brands', [ProductController::class, 'getBrands']);
        Route::get('/categories', [ProductController::class, 'getCategories']);
        Route::get('/on-sale', [ProductController::class, 'getOnSaleProducts']);
        Route::post('/bulk', [ProductController::class, 'bulkOperation']);
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
        Route::patch('/{product}/stock', [ProductController::class, 'updateStock']);
        Route::patch('/{product}/discount', [ProductController::class, 'applyDiscount']);
        Route::delete('/{product}/images', [ProductController::class, 'deleteImage']);
        Route::delete('/{product}/videos', [ProductController::class, 'deleteVideo']);
    });
});
