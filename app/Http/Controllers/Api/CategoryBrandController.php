<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoryBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CategoryBrandController extends Controller
{
    // ==================== PUBLIC ENDPOINTS ====================

    /**
     * Display a listing of category brands for public use.
     * GET /api/public/category-brands
     */
    public function publicIndex()
    {
        $categoryBrands = CategoryBrand::ordered()->get();
        return response()->json([
            'success' => true,
            'data' => $categoryBrands,
        ]);
    }

    /**
     * Display the specified category brand by slug for public use.
     * GET /api/public/category-brands/{slug}
     */
    public function publicShow($slug)
    {
        $categoryBrand = CategoryBrand::bySlug($slug)->first();
        
        if (!$categoryBrand) {
            return response()->json(['success' => false, 'message' => 'Category brand not found'], 404);
        }
        
        return response()->json(['success' => true, 'data' => $categoryBrand]);
    }

    // ==================== ADMIN ENDPOINTS ====================

    /**
     * Display a listing of category brands.
     * GET /api/admin/category-brands
     */
    public function index()
    {
        $categoryBrands = CategoryBrand::ordered()->get();
        return response()->json([
            'success' => true,
            'data' => $categoryBrands,
        ]);
    }

    /**
     * Store a newly created category brand.
     * POST /api/admin/category-brands
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('category-brands', 'public');
            $data['logo'] = '/storage/' . $path;
        }

        // Auto-generate ordering if not provided
        if (!isset($data['ordering'])) {
            $data['ordering'] = (CategoryBrand::max('ordering') ?? 0) + 1;
        }

        $categoryBrand = CategoryBrand::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category brand created successfully',
            'data' => $categoryBrand,
        ], 201);
    }

    /**
     * Display the specified category brand.
     * GET /api/admin/category-brands/{id}
     */
    public function show($id)
    {
        $categoryBrand = CategoryBrand::find($id);
        
        if (!$categoryBrand) {
            return response()->json(['success' => false, 'message' => 'Category brand not found'], 404);
        }
        
        return response()->json(['success' => true, 'data' => $categoryBrand]);
    }

    /**
     * Update the specified category brand.
     * PUT/PATCH /api/admin/category-brands/{id}
     */
    public function update(Request $request, $id)
    {
        $categoryBrand = CategoryBrand::find($id);

        if (!$categoryBrand) {
            return response()->json(['success' => false, 'message' => 'Category brand not found'], 404);
        }

        $data = $this->validateData($request, $id);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            $this->deleteFileIfExists($categoryBrand->logo);
            $path = $request->file('logo')->store('category-brands', 'public');
            $data['logo'] = '/storage/' . $path;
        }

        $categoryBrand->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Category brand updated successfully',
            'data' => $categoryBrand,
        ]);
    }

    /**
     * Remove the specified category brand.
     * DELETE /api/admin/category-brands/{id}
     */
    public function destroy($id)
    {
        $categoryBrand = CategoryBrand::find($id);

        if (!$categoryBrand) {
            return response()->json(['success' => false, 'message' => 'Category brand not found'], 404);
        }

        // Delete logo file if exists
        $this->deleteFileIfExists($categoryBrand->logo);

        $categoryBrand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category brand deleted successfully',
        ]);
    }

    /**
     * Update ordering of category brands.
     * PATCH /api/admin/category-brands/{id}/ordering
     */
    public function updateOrdering(Request $request, $id)
    {
        $categoryBrand = CategoryBrand::find($id);
        
        if (!$categoryBrand) {
            return response()->json(['success' => false, 'message' => 'Category brand not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'ordering' => ['required', 'integer', 'min:0'],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $categoryBrand->update(['ordering' => $request->input('ordering')]);
        
        return response()->json([
            'success' => true,
            'message' => 'Category brand ordering updated successfully',
            'data' => $categoryBrand,
        ]);
    }

    // ==================== PRIVATE METHODS ====================

    private function validateData(Request $request, $id = null): array
    {
        $rules = [
            'brand' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'], // 5 MB
            'ordering' => ['nullable', 'integer', 'min:0'],
        ];

        // Add unique validation for slug
        if ($id) {
            $rules['slug'][] = 'unique:category_brands,slug,' . $id;
        } else {
            $rules['slug'][] = 'unique:category_brands,slug';
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
        if (empty($data['slug']) && !empty($data['brand'])) {
            $data['slug'] = Str::slug($data['brand']);
        }

        return $data;
    }

    /**
     * Create category brand from remote logo URL.
     * POST /api/admin/category-brands/url
     */
    public function fromUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:category_brands,slug'],
            'logo_url' => ['required', 'url'],
            'ordering' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $logoUrl = $request->input('logo_url');

        try {
            $client = new Client(['timeout' => 30]);
            $response = $client->get($logoUrl);
            $contentType = $response->getHeader('Content-Type')[0] ?? '';

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg',
            ];

            if (!isset($allowed[$contentType])) {
                return response()->json(['success' => false, 'message' => 'Invalid image format'], 422);
            }

            $ext = $allowed[$contentType];

            // Enforce max size ~ 5 MB
            $maxBytes = 5 * 1024 * 1024;
            $body = '';
            $stream = $response->getBody();
            while (!$stream->eof()) {
                $chunk = $stream->read(1024 * 64);
                $body .= $chunk;
                if (strlen($body) > $maxBytes) {
                    return response()->json(['success' => false, 'message' => 'Logo exceeds 5MB limit'], 422);
                }
            }

            // Generate unique filename
            $filename = 'category-brands/' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $ext;
            Storage::disk('public')->put($filename, $body);

            // Auto-generate ordering if not provided
            $nextOrdering = (CategoryBrand::max('ordering') ?? 0) + 1;
            $payload = [
                'brand' => $request->input('brand'),
                'slug' => $request->input('slug') ?: Str::slug($request->input('brand')),
                'logo' => '/storage/' . $filename,
                'ordering' => $request->filled('ordering') ? (int) $request->input('ordering') : $nextOrdering,
            ];

            $categoryBrand = CategoryBrand::create($payload);

            return response()->json([
                'success' => true,
                'message' => 'Category brand created successfully from URL',
                'data' => $categoryBrand,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('fromUrl error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to download logo'], 500);
        }
    }

    /**
     * Update category brand logo from remote URL.
     * PATCH /api/admin/category-brands/{id}/url
     */
    public function updateFromUrl(Request $request, $id)
    {
        $categoryBrand = CategoryBrand::find($id);

        if (!$categoryBrand) {
            return response()->json(['success' => false, 'message' => 'Category brand not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'logo_url' => ['required', 'url'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $logoUrl = $request->input('logo_url');

        try {
            $client = new Client(['timeout' => 30]);
            $response = $client->get($logoUrl);
            $contentType = $response->getHeader('Content-Type')[0] ?? '';

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg',
            ];

            if (!isset($allowed[$contentType])) {
                return response()->json(['success' => false, 'message' => 'Invalid image format'], 422);
            }

            $ext = $allowed[$contentType];

            // Enforce max size ~ 5 MB
            $maxBytes = 5 * 1024 * 1024;
            $body = '';
            $stream = $response->getBody();
            while (!$stream->eof()) {
                $chunk = $stream->read(1024 * 64);
                $body .= $chunk;
                if (strlen($body) > $maxBytes) {
                    return response()->json(['success' => false, 'message' => 'Logo exceeds 5MB limit'], 422);
                }
            }

            // Delete old logo if exists
            $this->deleteFileIfExists($categoryBrand->logo);

            // Generate unique filename
            $filename = 'category-brands/' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $ext;
            Storage::disk('public')->put($filename, $body);

            $categoryBrand->logo = '/storage/' . $filename;
            $categoryBrand->save();

            return response()->json([
                'success' => true,
                'message' => 'Category brand logo updated successfully',
                'data' => $categoryBrand,
            ]);

        } catch (\Throwable $e) {
            Log::error('updateFromUrl error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to download logo'], 500);
        }
    }

    /**
     * Delete file if it exists in storage
     */
    private function deleteFileIfExists($filePath)
    {
        if (!$filePath) return;

        // Extract the storage path from the full URL
        $path = ltrim(parse_url($filePath, PHP_URL_PATH), '/storage/');

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
