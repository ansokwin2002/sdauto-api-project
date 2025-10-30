<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
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
        $validator = Validator::make($request->all(), [
            'brand_name' => ['required', 'string', 'max:255', 'unique:brands,brand_name'],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $brand = Brand::create($request->only(['brand_name']));
        
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

        $validator = Validator::make($request->all(), [
            'brand_name' => ['required', 'string', 'max:255', 'unique:brands,brand_name,' . $id],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $brand->update($request->only(['brand_name']));
        
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
}
