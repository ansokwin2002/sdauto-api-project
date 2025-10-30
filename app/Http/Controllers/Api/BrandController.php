<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    // ==================== PUBLIC ENDPOINTS ====================

    /**
     * Display a listing of brands for public use.
     * GET /api/public/brands
     */
    public function publicIndex()
    {
        $brands = Brand::orderBy('brand_name')->get();
        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * Display the specified brand by slug for public use.
     * GET /api/public/brands/{slug}
     */
    public function publicShow($slug)
    {
        $brand = Brand::bySlug($slug)->with(['products' => function($query) {
            $query->where('is_active', true)->orderBy('name');
        }])->first();

        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $brand]);
    }

    // ==================== ADMIN ENDPOINTS ====================

    /**
     * Display a listing of brands.
     * GET /api/admin/brands
     */
    public function index()
    {
        $brands = Brand::withCount('products')->orderBy('brand_name')->get();
        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * Store a newly created brand.
     * POST /api/admin/brands
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $brand = Brand::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'data' => $brand
        ], 201);
    }

    /**
     * Display the specified brand.
     * GET /api/admin/brands/{id}
     */
    public function show($id)
    {
        $brand = Brand::withCount('products')->find($id);
        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $brand]);
    }

    /**
     * Update the specified brand.
     * PUT/PATCH /api/admin/brands/{id}
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand not found'], 404);
        }

        $data = $this->validateData($request, $id);
        $brand->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'data' => $brand
        ]);
    }

    /**
     * Remove the specified brand.
     * DELETE /api/admin/brands/{id}
     */
    public function destroy($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand not found'], 404);
        }

        // Check if brand has products
        $productCount = $brand->products()->count();
        if ($productCount > 0) {
            return response()->json([
                'success' => false, 
                'message' => "Cannot delete brand. It has {$productCount} associated products."
            ], 422);
        }

        $brand->delete();
        return response()->json([
            'success' => true, 
            'message' => 'Brand deleted successfully'
        ]);
    }

    /**
     * Public API: Display a listing of brands for frontend.
     * GET /api/public/brands
     */
    public function publicIndex()
    {
        $brands = Brand::withCount('products')->orderBy('brand_name')->get();
        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * Public API: Display the specified brand for frontend.
     * GET /api/public/brands/{id}
     */
    public function publicShow($id)
    {
        $brand = Brand::withCount('products')->find($id);
        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $brand]);
    }

    /**
     * Get brands with their products.
     * GET /api/admin/brands/{id}/products
     */
    public function getBrandProducts($id)
    {
        $brand = Brand::with(['products' => function($query) {
            $query->where('is_active', true)->orderBy('name');
        }])->find($id);
        
        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'brand' => $brand,
                'products' => $brand->products
            ]
        ]);
    }

    // ==================== PRIVATE METHODS ====================

    private function validateData(Request $request, $id = null): array
    {
        $rules = [
            'brand_name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
        ];

        // Add unique validation
        if ($id) {
            $rules['brand_name'][] = 'unique:brands,brand_name,' . $id;
            $rules['slug'][] = 'unique:brands,slug,' . $id;
        } else {
            $rules['brand_name'][] = 'unique:brands,brand_name';
            $rules['slug'][] = 'unique:brands,slug';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            abort(response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422));
        }

        $data = $validator->validated();

        // Auto-generate slug if not provided
        if (empty($data['slug']) && !empty($data['brand_name'])) {
            $data['slug'] = Str::slug($data['brand_name']);
        }

        return $data;
    }
}
