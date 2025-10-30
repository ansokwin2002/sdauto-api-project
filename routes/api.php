<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\PolicyController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DeliveryPartnerController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\FooterController;
use App\Http\Controllers\Api\CategoryBrandController;

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

// Public routes for frontend website (no authentication required)
Route::prefix('public')->group(function () {
    // Public Products - for frontend display
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']); // List products with filters
        Route::get('/brands', [ProductController::class, 'getBrands']); // Get all brands
        Route::get('/categories', [ProductController::class, 'getCategories']); // Get all categories
        Route::get('/on-sale', [ProductController::class, 'getOnSaleProducts']); // Get sale products
        Route::get('/{product}', [ProductController::class, 'show'])->missing(function (Request $request) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }); // Single product details
    });

    // Public Sliders - for homepage carousel
    Route::get('/sliders', [SliderController::class, 'index']);

    // Public Settings - for site configuration, contact info, etc.
    Route::get('/settings', [SettingController::class, 'index']);

    // Public Policies - for privacy policy, terms, etc.
    Route::prefix('policies')->group(function () {
        Route::get('/', [PolicyController::class, 'index']); // All policies
        Route::get('/{id}', [PolicyController::class, 'show']); // Single policy
    });

    // Public FAQs - for help section
    Route::prefix('faqs')->group(function () {
        Route::get('/', [FaqController::class, 'index']); // All FAQs
        Route::get('/{id}', [FaqController::class, 'show']); // Single FAQ
    });

    // Public Contact Info - for contact page
    Route::prefix('contact')->group(function () {
        Route::get('/', [ContactController::class, 'index']); // Contact information
        Route::post('/', [ContactController::class, 'store']); // Submit contact form (public)
    });

    // Public Shipping Info - for shipping rates/info
    Route::get('/shipping', [\App\Http\Controllers\Api\ShippingController::class, 'index']);

    // Public Delivery Partners - for frontend display
    Route::prefix('delivery-partners')->group(function () {
        Route::get('/', [DeliveryPartnerController::class, 'publicIndex']); // All delivery partners
        Route::get('/{id}', [DeliveryPartnerController::class, 'publicShow']); // Single delivery partner
    });

    // Public Brands - for frontend display
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'publicIndex']); // All brands
        Route::get('/{slug}', [BrandController::class, 'publicShow']); // Single brand by slug
    });

    // Public Footer - for website footer
    Route::get('/footer', [FooterController::class, 'index']);

    // Public Category Brands - for frontend display
    Route::prefix('category-brands')->group(function () {
        Route::get('/', [CategoryBrandController::class, 'publicIndex']); // All category brands
        Route::get('/{slug}', [CategoryBrandController::class, 'publicShow']); // Single category brand by slug
    });
});

// Protected routes with Sanctum (Admin/Management only)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });

    // Admin: Home Settings Management (CRUD)
    Route::prefix('admin/settings')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::post('/', [SettingController::class, 'store']);
        Route::get('/{id}', [SettingController::class, 'show']);
        Route::put('/{id}', [SettingController::class, 'update']);
        Route::patch('/{id}', [SettingController::class, 'update']);
    });

    // Admin: Slider Management (CRUD, file upload)
    Route::prefix('admin/sliders')->group(function () {
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

    // Admin: Shipping Management (no delete per request)
    Route::prefix('admin/shipping')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ShippingController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\ShippingController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\ShippingController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\ShippingController::class, 'update']);
        Route::patch('/{id}', [\App\Http\Controllers\Api\ShippingController::class, 'update']);
    });

    // Admin: Policy Management
    Route::prefix('admin/policies')->group(function () {
        Route::get('/', [PolicyController::class, 'index']);
        Route::post('/', [PolicyController::class, 'store']);
        Route::get('/{id}', [PolicyController::class, 'show']);
        Route::put('/{id}', [PolicyController::class, 'update']);
        Route::patch('/{id}', [PolicyController::class, 'update']);
    });

    // Admin: FAQ Management
    Route::prefix('admin/faqs')->group(function () {
        Route::get('/', [FaqController::class, 'index']);
        Route::post('/', [FaqController::class, 'store']);
        Route::get('/{id}', [FaqController::class, 'show']);
        Route::put('/{id}', [FaqController::class, 'update']);
        Route::patch('/{id}', [FaqController::class, 'update']);
        Route::delete('/{id}', [FaqController::class, 'destroy']);
    });

    // Admin: Contact Management
    Route::prefix('admin/contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index']);
        Route::post('/', [ContactController::class, 'store']);
        Route::get('/{id}', [ContactController::class, 'show']);
        Route::put('/{id}', [ContactController::class, 'update']);
        Route::patch('/{id}', [ContactController::class, 'update']);
        Route::delete('/{id}', [ContactController::class, 'destroy']);
    });

    // Admin: Product Management
    Route::prefix('admin/products')->group(function () {
        Route::get('/stats', [ProductController::class, 'getStats']);
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

    // Admin: Delivery Partners Management (CRUD, file upload)
    Route::prefix('admin/delivery-partners')->group(function () {
        Route::get('/', [DeliveryPartnerController::class, 'index']);
        // multipart upload (field: image)
        Route::post('/', [DeliveryPartnerController::class, 'store']);
        Route::post('/upload', [DeliveryPartnerController::class, 'store']);
        // create from remote image URL
        Route::post('/url', [DeliveryPartnerController::class, 'fromUrl']);

        Route::get('/{id}', [DeliveryPartnerController::class, 'show']);
        Route::put('/{id}', [DeliveryPartnerController::class, 'update']);
        Route::patch('/{id}', [DeliveryPartnerController::class, 'update']);
        // update from remote image URL
        Route::patch('/{id}/url', [DeliveryPartnerController::class, 'updateFromUrl']);
        Route::delete('/{id}', [DeliveryPartnerController::class, 'destroy']);
    });

    // Admin: Brand Management (CRUD)
    Route::prefix('admin/brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::post('/', [BrandController::class, 'store']);
        Route::get('/{id}', [BrandController::class, 'show']);
        Route::put('/{id}', [BrandController::class, 'update']);
        Route::patch('/{id}', [BrandController::class, 'update']);
        Route::delete('/{id}', [BrandController::class, 'destroy']);
        // Get brand with its products
        Route::get('/{id}/products', [BrandController::class, 'getBrandProducts']);
    });

    // Admin: Footer Management (CRUD)
    Route::prefix('admin/footer')->group(function () {
        Route::get('/', [FooterController::class, 'index']);
        Route::post('/', [FooterController::class, 'store']);
        Route::get('/{id}', [FooterController::class, 'show']);
        Route::put('/{id}', [FooterController::class, 'update']);
        Route::patch('/{id}', [FooterController::class, 'update']);
        Route::delete('/{id}', [FooterController::class, 'destroy']);
    });

    // Admin: Category Brand Management (CRUD)
    Route::prefix('admin/category-brands')->group(function () {
        Route::get('/', [CategoryBrandController::class, 'index']);
        // multipart upload (field: logo)
        Route::post('/', [CategoryBrandController::class, 'store']);
        Route::post('/upload', [CategoryBrandController::class, 'store']);
        // create from remote logo URL
        Route::post('/url', [CategoryBrandController::class, 'fromUrl']);

        Route::get('/{id}', [CategoryBrandController::class, 'show']);
        Route::put('/{id}', [CategoryBrandController::class, 'update']);
        Route::patch('/{id}', [CategoryBrandController::class, 'update']);
        Route::patch('/{id}/ordering', [CategoryBrandController::class, 'updateOrdering']);
        // update logo from remote URL
        Route::patch('/{id}/url', [CategoryBrandController::class, 'updateFromUrl']);
        Route::delete('/{id}', [CategoryBrandController::class, 'destroy']);
    });
});
