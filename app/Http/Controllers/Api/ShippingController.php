<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    /**
     * Copy storage/app/public → public/storage (no symlink needed)
     */
    private function syncPublicStorage()
    {
        File::copyDirectory(
            storage_path('app/public'),
            public_path('storage')
        );
    }

    // GET /api/shippings
    public function index()
    {
        $items = Shipping::orderByDesc('id')->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    // POST /api/shippings
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'label_partner' => ['nullable','string','max:255'],
            'text' => ['nullable','string'],
            'map_image' => ['nullable','image','mimes:jpg,jpeg,png,webp,gif','max:5120'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $payload = $request->only(['title','description','label_partner','text']);

        // Upload image (no symlink)
        if ($request->hasFile('map_image')) {
            $path = $request->file('map_image')->store('shippings', 'public');
            $payload['map_image'] = '/storage/' . $path;

            $this->syncPublicStorage();
        }

        $item = Shipping::create($payload);

        return response()->json(['success' => true, 'message' => 'Shipping created', 'data' => $item], 201);
    }

    // GET /api/shippings/{id}
    public function show($id)
    {
        $item = Shipping::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Shipping not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    // PUT /api/shippings/{id}
    public function update(Request $request, $id)
    {
        $item = Shipping::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Shipping not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes','required','string','max:255'],
            'description' => ['nullable','string'],
            'label_partner' => ['nullable','string','max:255'],
            'text' => ['nullable','string'],
            'map_image' => ['nullable','image','mimes:jpg,jpeg,png,webp,gif','max:5120'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $item->fill($request->only(['title','description','label_partner','text']));

        if ($request->hasFile('map_image')) {
            $this->deleteFileIfExists($item->map_image);

            $path = $request->file('map_image')->store('shippings', 'public');
            $item->map_image = '/storage/' . $path;

            $this->syncPublicStorage();
        }

        $item->save();

        return response()->json(['success' => true, 'message' => 'Shipping updated', 'data' => $item]);
    }

    /**
     * Delete physical file from /public/storage AND disk('public')
     */
    private function deleteFileIfExists($publicPath)
    {
        if (!$publicPath) return;

        // Example: /storage/shippings/img.jpg → shippings/img.jpg
        $cleanPath = str_replace('/storage/', '', $publicPath);

        if (Storage::disk('public')->exists($cleanPath)) {
            Storage::disk('public')->delete($cleanPath);
        }

        $fullPublicPath = public_path('storage/' . $cleanPath);
        if (file_exists($fullPublicPath)) {
            unlink($fullPublicPath);
        }
    }
}
