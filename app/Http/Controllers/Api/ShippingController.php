<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    // GET /api/shippings
    public function index()
    {
        $items = Shipping::orderByDesc('id')->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    // POST /api/shippings (multipart or JSON)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'label_partner' => ['nullable','string','max:255'],
            'text' => ['nullable','string'],
            'map_image' => ['nullable','image','mimes:jpg,jpeg,png,webp,gif','max:5120']
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $payload = $request->only(['title','description','label_partner','text']);

        // Handle map image via upload
        if ($request->hasFile('map_image')) {
            $path = $request->file('map_image')->store('shippings', 'public');
            $payload['map_image'] = '/storage/' . $path;
        }

        $item = Shipping::create($payload);
        return response()->json(['success' => true, 'message' => 'Shipping created', 'data' => $item], 201);
    }

    // GET /api/shippings/{id}
    public function show($id)
    {
        $item = Shipping::find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Shipping not found'], 404);
        return response()->json(['success' => true, 'data' => $item]);
    }

    // PUT/PATCH /api/shippings/{id}
    public function update(Request $request, $id)
    {
        $item = Shipping::find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Shipping not found'], 404);

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes','required','string','max:255'],
            'description' => ['nullable','string'],
            'label_partner' => ['nullable','string','max:255'],
            'text' => ['nullable','string'],
            'map_image' => ['nullable','image','mimes:jpg,jpeg,png,webp,gif','max:5120']
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $item->fill($request->only(['title','description','label_partner','text']));

        if ($request->hasFile('map_image')) {
            $this->deleteFileIfExists($item->map_image);
            $path = $request->file('map_image')->store('shippings', 'public');
            $item->map_image = '/storage/' . $path;
        }

        $item->save();
        return response()->json(['success' => true, 'message' => 'Shipping updated', 'data' => $item]);
    }

    private function deleteFileIfExists($publicPath)
    {
        if (!$publicPath) return;
        $relative = ltrim($publicPath, '/');
        if (str_starts_with($relative, 'storage/')) {
            $diskPath = substr($relative, strlen('storage/'));
            Storage::disk('public')->delete($diskPath);
        }
    }
}
