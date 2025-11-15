<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // ==================== PUBLIC ENDPOINTS ====================

    /**
     * Display a listing of categories for public use.
     * GET /api/public/categories
     */
    public function publicIndex()
    {
        $categories = Category::where('status', true)->orderBy('sort_order')->get();
        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Display the specified category by slug for public use.
     * GET /api/public/categories/{slug}
     */
    public function publicShow($slug)
    {
        $category = Category::where('slug', $slug)->where('status', true)->first();

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $category]);
    }

    // ==================== ADMIN ENDPOINTS ====================

    /**
     * Display a listing of categories.
     * GET /api/admin/categories
     */
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();
        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created category.
     * POST /api/admin/categories
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified category.
     * GET /api/admin/categories/{id}
     */
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $category]);
    }

    /**
     * Update the specified category.
     * PUT/PATCH /api/admin/categories/{id}
     */
    public function update(Request $request, $id)    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $data = $this->validateData($request, $id);
        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified category.
     * DELETE /api/admin/categories/{id}
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $category->delete();
        return response()->json([
            'success' => true, 
            'message' => 'Category deleted successfully'
        ]);
    }

    // ==================== PRIVATE METHODS ====================

    private function validateData(Request $request, $id = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ];

        // Add unique validation
        if ($id) {
            $rules['name'][] = 'unique:categories,name,' . $id;
            $rules['slug'][] = 'unique:categories,slug,' . $id;
        } else {
            $rules['name'][] = 'unique:categories,name';
            $rules['slug'][] = 'unique:categories,slug';
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
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }
}
