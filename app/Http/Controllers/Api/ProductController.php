<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\BulkProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     * GET /api/products
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with('brand');

            // Search functionality
            /*
            if ($request->filled('search')) {
                $query->search($request->get('search'));
            }

            // Filter by brand
            if ($request->filled('brand')) {
                $query->where('brand', $request->get('brand'));
            }

            // Filter by category
            if ($request->filled('category')) {
                $query->where('category', $request->get('category'));
            }

            // Filter by condition
            if ($request->filled('condition')) {
                $query->where('condition', $request->get('condition'));
            }

            // Filter by stock status
            if ($request->filled('stock_status')) {
                $stockStatus = $request->get('stock_status');
                switch ($stockStatus) {
                    case 'in_stock':
                        $query->inStock();
                        break;
                    case 'out_of_stock':
                        $query->outOfStock();
                        break;
                    case 'low_stock':
                        $query->lowStock();
                        break;
                }
            }

            // Filter by discount/sale status
            if ($request->filled('on_sale')) {
                if ($request->boolean('on_sale')) {
                    $query->onDiscount();
                }
            }
            */

            // Filter by active status
            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            } else {
                $query->active(); // Default to active products
            }

            // Price range filter
            /*
            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->get('min_price'));
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->get('max_price'));
            }

            // Original price range filter
            if ($request->filled('min_original_price')) {
                $query->where('original_price', '>=', $request->get('min_original_price'));
            }
            if ($request->filled('max_original_price')) {
                $query->where('original_price', '<=', $request->get('max_original_price'));
            }

            // Quantity range filter
            if ($request->filled('min_quantity')) {
                $query->where('quantity', '>=', $request->get('min_quantity'));
            }
            if ($request->filled('max_quantity')) {
                $query->where('quantity', '<=', $request->get('max_quantity'));
            }
            */

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort fields
            $allowedSortFields = ['id', 'name', 'brand', 'category', 'price', 'original_price', 'quantity', 'created_at', 'updated_at'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }

            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');


            // Pagination
            $perPage = min($request->get('per_page', 10), 100);
            $products = $query->paginate($perPage);

            return new ProductCollection($products);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching products',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a newly created product
     * POST /api/products
     */
    public function store(ProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle brand logic - if brand string is provided, find or create brand
            if ($request->filled('brand') && !$request->filled('brand_id')) {
                $brand = \App\Models\Brand::firstOrCreate(['brand_name' => $request->input('brand')]);
                $data['brand_id'] = $brand->id;
            }

            $imagePaths = [];

            if ($request->has('image_urls')) {
                $convertedImageUrls = array_map(function($url) {
                    return $this->convertToRelativePath($url);
                }, $request->input('image_urls'));
                $imagePaths = array_merge($imagePaths, $convertedImageUrls);
            }

            if ($request->hasFile('images')) {
                // Ensure the products directory exists in storage/app/public
                $productsPath = storage_path('app/public/products');
                if (!\Illuminate\Support\Facades\File::exists($productsPath)) {
                    \Illuminate\Support\Facades\File::makeDirectory($productsPath, 0755, true);
                }

                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = 'storage/' . $path;
                }
            }

            $data['images'] = $imagePaths;
            $videoIds = [];
            if ($request->has('videos')) {
                foreach ($request->input('videos') as $videoUrl) {
                    $youtubeRule = new YoutubeUrl();
                    $youtubeRule->validate('videos', $videoUrl, function ($message) {
                        // Handle validation failure if necessary, though ProductRequest should catch it
                    });
                    if ($youtubeRule->videoId()) {
                        $videoIds[] = $youtubeRule->videoId();
                    }
                }
            }
            $data['videos'] = $videoIds;

            $product = Product::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => new ProductResource($product)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating product',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified product
     * GET /api/products/{id}
     */
    public function show(Product $product)
    {
        try {
            $product->load('brand');
            return response()->json([
                'success' => true,
                'data' => new ProductResource($product)
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified product
     * PUT/PATCH /api/products/{id}
     */
    public function update(ProductRequest $request, Product $product)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle brand logic - if brand string is provided, find or create brand
            if ($request->filled('brand') && !$request->filled('brand_id')) {
                $brand = \App\Models\Brand::firstOrCreate(['brand_name' => $request->input('brand')]);
                $data['brand_id'] = $brand->id;
            }

            // Handle image updates
            if ($request->has('deleted_images') || $request->hasFile('images') || $request->has('image_urls')) {
                $currentImages = $product->images ?? [];

                // Remove deleted images
                if ($request->has('deleted_images')) {
                    $imagesToDelete = $request->input('deleted_images');
                    foreach ($imagesToDelete as $imageUrl) {
                        $path = ltrim(parse_url($imageUrl, PHP_URL_PATH), '/storage/');
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                        // Remove from current images
                        if (($key = array_search($imageUrl, $currentImages)) !== false) {
                            unset($currentImages[$key]);
                        }
                    }
                }

                $newImagePaths = [];
                // Add new image URLs
                if ($request->has('image_urls')) {
                    $convertedImageUrls = array_map(function($url) {
                        return $this->convertToRelativePath($url);
                    }, $request->input('image_urls'));
                    $newImagePaths = array_merge($newImagePaths, $convertedImageUrls);
                }

                // Add newly uploaded images
                if ($request->hasFile('images')) {
                    // Ensure the products directory exists in storage/app/public
                    $productsPath = storage_path('app/public/products');
                    if (!\Illuminate\Support\Facades\File::exists($productsPath)) {
                        \Illuminate\Support\Facades\File::makeDirectory($productsPath, 0755, true);
                    }
                    
                    foreach ($request->file('images') as $image) {
                        $path = $image->store('products', 'public');
                        $newImagePaths[] = 'storage/' . $path;
                    }
                }
                
                // Merge and re-index the array
                $data['images'] = array_values(array_unique(array_merge($currentImages, $newImagePaths)));
            }

            // Handle video updates
            if ($request->has('deleted_videos') || $request->has('videos')) {
                $currentVideos = $product->videos ?? [];

                // Remove deleted videos
                if ($request->has('deleted_videos')) {
                    $videosToDelete = $request->input('deleted_videos');
                    foreach ($videosToDelete as $videoUrl) {
                        $youtubeRule = new YoutubeUrl();
                        $youtubeRule->validate('videos', $videoUrl, function ($message) {});
                        $videoIdToDelete = $youtubeRule->videoId();

                        if (($key = array_search($videoIdToDelete, $currentVideos)) !== false) {
                            unset($currentVideos[$key]);
                        }
                    }
                }

                $newVideoIds = [];
                // Add new video URLs
                if ($request->has('videos')) {
                    foreach ($request->input('videos') as $videoUrl) {
                        $youtubeRule = new YoutubeUrl();
                        $youtubeRule->validate('videos', $videoUrl, function ($message) {});
                        if ($youtubeRule->videoId()) {
                            $newVideoIds[] = $youtubeRule->videoId();
                        }
                    }
                }

                // Merge and re-index the array
                $data['videos'] = array_values(array_unique(array_merge($currentVideos, $newVideoIds)));
            }

            $product->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => new ProductResource($product->fresh())
            ]);

        } catch (QueryException $e) {
            DB::rollBack();
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) { // 1062 is the error code for duplicate entry in MySQL
                return response()->json([
                    'success' => false,
                    'message' => 'The part number already exists for another product.'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error updating product',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error updating product',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified product
     * DELETE /api/products/{id}
     */
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete an image from a product
     * DELETE /api/products/{id}/images
     */
    public function deleteImage(Request $request, Product $product)
    {
        $request->validate([
            'image_url' => 'required|string|url'
        ]);

        try {
            DB::beginTransaction();

            $imageUrl = $request->input('image_url');
            $images = $product->images;

            // Find the index of the image to delete
            $imageToDeleteRelativePath = $this->convertToRelativePath($imageUrl);
            $imageIndex = array_search($imageToDeleteRelativePath, $images);

            if ($imageIndex === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found on product'
                ], 404);
            }

            // Get the path of the image from the URL
            $path = $this->convertToRelativePath($imageUrl);

            // Delete the physical file
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // Remove the image from the array
            array_splice($images, $imageIndex, 1);

            // Re-index the array
            $product->images = array_values($images);
            $product->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
                'data' => new ProductResource($product->fresh())
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting image',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete a video from a product
     * DELETE /api/products/{id}/videos
     */
    public function deleteVideo(Request $request, Product $product)
    {
        $request->validate([
            'video_url' => 'required|string|url'
        ]);

        try {
            DB::beginTransaction();

            $videoUrl = $request->input('video_url');
            $currentVideos = $product->videos ?? [];

            $youtubeRule = new YoutubeUrl();
            $youtubeRule->validate('videos', $videoUrl, function ($message) {
                // Validation message can be ignored here as it's already validated by ProductRequest
            });
            $videoIdToDelete = $youtubeRule->videoId();

            if ($videoIdToDelete === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid YouTube video URL provided.'
                ], 400);
            }

            // Find the index of the video ID to delete
            $videoIndex = array_search($videoIdToDelete, $currentVideos);

            if ($videoIndex === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found on product'
                ], 404);
            }

            // Remove the video ID from the array
            unset($currentVideos[$videoIndex]);

            // Re-index the array
            $product->videos = array_values($currentVideos);
            $product->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully',
                'data' => new ProductResource($product->fresh())
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting video',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Converts a full URL to a relative storage path if applicable.
     *
     * @param string $url
     * @return string
     */
    private function convertToRelativePath(string $url): string
    {
        // Check if the URL contains 'storage/' to determine if it's a full storage URL
        if (strpos($url, '/storage/') !== false) {
            // Parse the URL and get the path component
            $path = parse_url($url, PHP_URL_PATH);
            // Remove everything before 'storage/'
            return ltrim(strstr($path, 'storage/'), '/');
        }
        return $url; // Return original if not a full storage URL
    }

    /**
     * Get all unique brands
     * GET /api/products/brands
     */
    public function getBrands()
    {
        try {
            $brands = \App\Models\Brand::withCount(['products' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('brand_name')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $brands
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching brands',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all unique categories
     * GET /api/products/categories
     */
    public function getCategories()
    {
        try {
            $categories = Product::select('category')
                ->distinct()
                ->where('is_active', true)
                ->whereNotNull('category')
                ->orderBy('category')
                ->pluck('category')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get products on sale/discount
     * GET /api/products/on-sale
     */
    public function getOnSaleProducts(Request $request)
    {
        try {
            $query = Product::onDiscount()->active();

            // Pagination
            $perPage = min($request->get('per_page', 15), 100);
            $products = $query->paginate($perPage);

            return new ProductCollection($products);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching sale products',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update product stock quantity
     * PATCH /api/products/{id}/stock
     */
    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:999999',
            'operation' => 'in:set,add,subtract'
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($id);
            
            $operation = $request->get('operation', 'set');
            $quantity = $request->get('quantity');

            switch ($operation) {
                case 'add':
                    $newQuantity = $product->quantity + $quantity;
                    break;
                case 'subtract':
                    $newQuantity = max(0, $product->quantity - $quantity);
                    break;
                default: // 'set'
                    $newQuantity = $quantity;
                    break;
            }

            $product->quantity = min($newQuantity, 999999);
            $product->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => new ProductResource($product)
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating stock',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Apply discount to product
     * PATCH /api/products/{id}/discount
     */
    public function applyDiscount(Request $request, $id)
    {
        $request->validate([
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'set_original_price' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($id);
            $discountPercentage = $request->get('discount_percentage');
            $setOriginalPrice = $request->boolean('set_original_price', true);

            // Set original price if not set and requested
            if ($setOriginalPrice && !$product->original_price) {
                $product->original_price = $product->price;
            }

            // Calculate new price
            $basePrice = $product->original_price ?? $product->price;
            $discountAmount = $basePrice * ($discountPercentage / 100);
            $product->price = $basePrice - $discountAmount;

            $product->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Discount applied successfully',
                'data' => new ProductResource($product)
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error applying discount',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Bulk operations
     * POST /api/products/bulk
     */
    public function bulkOperation(BulkProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $productIds = $request->get('product_ids');
            $action = $request->get('action');

            switch ($action) {
                case 'activate':
                    Product::whereIn('id', $productIds)->update(['is_active' => true]);
                    $message = 'Products activated successfully';
                    break;
                case 'deactivate':
                    Product::whereIn('id', $productIds)->update(['is_active' => false]);
                    $message = 'Products deactivated successfully';
                    break;
                case 'delete':
                    Product::whereIn('id', $productIds)->delete();
                    $message = 'Products deleted successfully';
                    break;
                case 'update_stock':
                    $quantity = $request->get('quantity');
                    $operation = $request->get('operation', 'set');
                    
                    $products = Product::whereIn('id', $productIds)->get();
                    foreach ($products as $product) {
                        switch ($operation) {
                            case 'add':
                                $product->quantity = min($product->quantity + $quantity, 999999);
                                break;
                            case 'subtract':
                                $product->quantity = max(0, $product->quantity - $quantity);
                                break;
                            default: // 'set'
                                $product->quantity = $quantity;
                                break;
                        }
                        $product->save();
                    }
                    $message = 'Stock updated successfully for all selected products';
                    break;
                case 'update_price':
                    $price = $request->get('price');
                    $originalPrice = $request->get('original_price');
                    
                    $updateData = ['price' => $price];
                    if ($originalPrice !== null) {
                        $updateData['original_price'] = $originalPrice;
                    }
                    
                    Product::whereIn('id', $productIds)->update($updateData);
                    $message = 'Prices updated successfully for all selected products';
                    break;
                case 'apply_discount':
                    $discountPercentage = $request->get('discount_percentage');
                    
                    $products = Product::whereIn('id', $productIds)->get();
                    foreach ($products as $product) {
                        // Set original price if not set
                        if (!$product->original_price) {
                            $product->original_price = $product->price;
                        }
                        
                        // Calculate new price
                        $basePrice = $product->original_price;
                        $discountAmount = $basePrice * ($discountPercentage / 100);
                        $product->price = $basePrice - $discountAmount;
                        
                        $product->save();
                    }
                    $message = "Discount of {$discountPercentage}% applied successfully to all selected products";
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'affected_count' => count($productIds)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk operation',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get product statistics
     * GET /api/products/stats
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_products' => Product::count(),
                'active_products' => Product::active()->count(),
                'inactive_products' => Product::where('is_active', false)->count(),
                'in_stock_products' => Product::inStock()->count(),
                'out_of_stock_products' => Product::outOfStock()->count(),
                'low_stock_products' => Product::lowStock()->count(),
                'products_on_sale' => Product::onDiscount()->count(),
                'total_inventory_value' => Product::sum(DB::raw('price * quantity')),
                'total_original_inventory_value' => Product::sum(DB::raw('COALESCE(original_price, price) * quantity')),
                'average_price' => Product::avg('price'),
                'average_original_price' => Product::whereNotNull('original_price')->avg('original_price'),
                'total_quantity' => Product::sum('quantity'),
                'total_discount_savings' => Product::whereNotNull('original_price')
                    ->sum(DB::raw('(original_price - price) * quantity')),
                'brands_count' => Product::distinct('brand')->count(),
                'categories_count' => Product::distinct('category')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
