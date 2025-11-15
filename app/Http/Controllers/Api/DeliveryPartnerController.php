<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class DeliveryPartnerController extends Controller
{
    /**
     * Display a listing of delivery partners.
     * GET /api/delivery-partners
     */
    public function index()
    {
        $items = DeliveryPartner::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Store a newly created delivery partner (multipart form-data with image file).
     * POST /api/delivery-partners
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'], // 5 MB
            'url_link' => ['nullable', 'url', 'max:500'],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $payload = $request->only(['title', 'description', 'url_link']);

        // Handle image via upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('delivery-partners', 'public');
            $payload['image'] = '/storage/' . $path;
        }

        $item = DeliveryPartner::create($payload);
        return response()->json([
            'success' => true, 
            'message' => 'Delivery partner created successfully', 
            'data' => $item
        ], 201);
    }

    /**
     * Display the specified delivery partner.
     * GET /api/delivery-partners/{id}
     */
    public function show($id)
    {
        $item = DeliveryPartner::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Delivery partner not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    /**
     * Update the specified delivery partner (can replace image).
     * PUT/PATCH /api/delivery-partners/{id}
     */
    public function update(Request $request, $id)
    {
        $item = DeliveryPartner::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Delivery partner not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'url_link' => ['nullable', 'url', 'max:500'],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Update only fields that are present in the request to avoid unintended overwrites
        if ($request->has('title')) {
            $item->title = $request->input('title');
        }
        if ($request->has('description')) {
            $item->description = $request->input('description');
        }
        if ($request->has('url_link')) {
            $item->url_link = $request->input('url_link');
        }

        if ($request->hasFile('image')) {
            // Delete old file if exists
            $this->deleteFileIfExists($item->image);
            $path = $request->file('image')->store('delivery-partners', 'public');
            $item->image = '/storage/' . $path;
        }

        $item->save();
        return response()->json([
            'success' => true, 
            'message' => 'Delivery partner updated successfully', 
            'data' => $item
        ]);
    }

    /**
     * Remove the specified delivery partner.
     * DELETE /api/delivery-partners/{id}
     */
    public function destroy($id)
    {
        $item = DeliveryPartner::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Delivery partner not found'], 404);
        }

        $this->deleteFileIfExists($item->image);
        $item->delete();
        return response()->json([
            'success' => true, 
            'message' => 'Delivery partner deleted successfully'
        ]);
    }

    /**
     * Create delivery partner from image URL.
     * POST /api/delivery-partners/url
     */
    public function fromUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['required', 'url'],
            'url_link' => ['nullable', 'url', 'max:500'],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $url = $request->input('image_url');

        try {
            $client = new Client(['timeout' => 30]);
            $response = $client->get($url);
            $contentType = $response->getHeaderLine('Content-Type');

            // Check if it's an image
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];

            if (!isset($allowed[$contentType])) {
                return response()->json(['success' => false, 'message' => 'URL does not point to a valid image'], 422);
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
                    return response()->json(['success' => false, 'message' => 'Image exceeds 5MB limit'], 422);
                }
            }

            // Generate unique filename
            $filename = 'delivery-partners/' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $ext;
            Storage::disk('public')->put($filename, $body);

            $payload = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'image' => '/storage/' . $filename,
                'url_link' => $request->input('url_link'),
            ];
            $item = DeliveryPartner::create($payload);

            return response()->json([
                'success' => true, 
                'message' => 'Delivery partner created successfully', 
                'data' => $item
            ], 201);
        } catch (\Throwable $e) {
            Log::error('fromUrl error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to download image'], 500);
        }
    }

    /**
     * Update delivery partner with image URL.
     * PATCH /api/delivery-partners/{id}/url
     */
    public function updateFromUrl(Request $request, $id)
    {
        $item = DeliveryPartner::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Delivery partner not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'url_link' => ['nullable', 'url', 'max:500'],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Update basic fields
        $item->fill($request->only(['title', 'description', 'url_link']));

        // Handle image URL if provided
        if ($request->filled('image_url')) {
            $url = $request->input('image_url');

            try {
                $client = new Client(['timeout' => 30]);
                $response = $client->get($url);
                $contentType = $response->getHeaderLine('Content-Type');

                // Check if it's an image
                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp'
                ];

                if (!isset($allowed[$contentType])) {
                    return response()->json(['success' => false, 'message' => 'URL does not point to a valid image'], 422);
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
                        return response()->json(['success' => false, 'message' => 'Image exceeds 5MB limit'], 422);
                    }
                }

                // Delete old file if exists
                $this->deleteFileIfExists($item->image);

                // Generate unique filename
                $filename = 'delivery-partners/' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $ext;
                Storage::disk('public')->put($filename, $body);

                $item->image = '/storage/' . $filename;
            } catch (\Throwable $e) {
                Log::error('updateFromUrl error: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Unable to download image'], 500);
            }
        }

        $item->save();
        return response()->json([
            'success' => true, 
            'message' => 'Delivery partner updated successfully', 
            'data' => $item
        ]);
    }

    /**
     * Public API: Display a listing of delivery partners for frontend.
     * GET /api/public/delivery-partners
     */
    public function publicIndex()
    {
        $items = DeliveryPartner::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Public API: Display the specified delivery partner for frontend.
     * GET /api/public/delivery-partners/{id}
     */
    public function publicShow($id)
    {
        $item = DeliveryPartner::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Delivery partner not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    /**
     * Delete file if it exists in storage
     */
    private function deleteFileIfExists($publicPath)
    {
        if (!$publicPath) return;
        // $publicPath example: /storage/delivery-partners/xyz.jpg -> convert to storage path
        $relative = ltrim($publicPath, '/'); // storage/delivery-partners/xyz.jpg
        if (str_starts_with($relative, 'storage/')) {
            $diskPath = substr($relative, strlen('storage/'));
            Storage::disk('public')->delete($diskPath);
        }
    }
}
