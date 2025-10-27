<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SliderController extends Controller
{
    // GET /api/sliders
    public function index()
    {
        $items = Slider::orderBy('ordering')->orderBy('id')->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    // POST /api/sliders (multipart form-data with image file)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['required','image','mimes:jpg,jpeg,png,webp,gif','max:5120'], // 5 MB
            'ordering' => ['nullable','integer','min:0']
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // store file
        $path = $request->file('image')->store('sliders', 'public');

        // Determine ordering: if not provided, set to max(ordering)+1 (starting at 1)
        $nextOrdering = (int) (Slider::max('ordering') ?? 0) + 1;
        $payload = [
            'image' => '/storage/' . $path,
            'ordering' => $request->filled('ordering') ? (int) $request->input('ordering') : $nextOrdering,
        ];
        $item = Slider::create($payload);

        return response()->json(['success' => true, 'message' => 'Slider created successfully', 'data' => $item], 201);
    }

    // GET /api/sliders/{id}
    public function show($id)
    {
        $item = Slider::find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Slider not found'], 404);
        return response()->json(['success' => true, 'data' => $item]);
    }

    // PUT/PATCH /api/sliders/{id} (can replace image)
    public function update(Request $request, $id)
    {
        $item = Slider::find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Slider not found'], 404);

        $validator = Validator::make($request->all(), [
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp,gif','max:1048576'],
            'ordering' => ['nullable','integer','min:0']
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('image')) {
            // delete old file if exists
            $this->deleteFileIfExists($item->image);
            $path = $request->file('image')->store('sliders', 'public');
            $item->image = '/storage/' . $path;
        }
        if ($request->filled('ordering')) {
            $item->ordering = (int) $request->input('ordering');
        }
        $item->save();

        return response()->json(['success' => true, 'message' => 'Slider updated successfully', 'data' => $item]);
    }

    // PATCH /api/sliders/{id}/ordering
    public function updateOrdering(Request $request, $id)
    {
        $item = Slider::find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Slider not found'], 404);

        $validator = Validator::make($request->all(), [
            'ordering' => ['required','integer','min:0']
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $item->ordering = (int) $request->input('ordering');
        $item->save();
        return response()->json(['success' => true, 'message' => 'Ordering updated successfully', 'data' => $item]);
    }

    // DELETE /api/sliders/{id}
    public function destroy($id)
    {
        $item = Slider::find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Slider not found'], 404);

        $this->deleteFileIfExists($item->image);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Slider deleted successfully']);
    }

    // POST /api/sliders/url { url: "https://..." }
    public function fromUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => ['required','url'],
            'ordering' => ['nullable','integer','min:0']
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $url = $request->input('url');

        try {
            $client = new Client([
                'timeout' => 15,
                'verify' => false, // in case of self-signed sources; consider enabling in production
                'headers' => [
                    'User-Agent' => 'SD-Auto-Slider/1.0'
                ],
                'allow_redirects' => [
                    'max' => 3
                ]
            ]);
            $response = $client->get($url, ['stream' => true]);
            $status = $response->getStatusCode();
            if ($status < 200 || $status >= 300) {
                return response()->json(['success' => false, 'message' => 'Failed to fetch image from URL'], 422);
            }

            $contentType = $response->getHeaderLine('Content-Type');
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            if (!isset($allowed[$contentType])) {
                return response()->json(['success' => false, 'message' => 'Unsupported image type: ' . $contentType], 422);
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
            $filename = 'sliders/' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $ext;
            Storage::disk('public')->put($filename, $body);

            $nextOrdering = (int) (Slider::max('ordering') ?? 0) + 1;
            $payload = [
                'image' => '/storage/' . $filename,
                'ordering' => $request->filled('ordering') ? (int) $request->input('ordering') : $nextOrdering,
            ];
            $item = Slider::create($payload);

            return response()->json(['success' => true, 'message' => 'Slider created successfully', 'data' => $item], 201);
        } catch (\Throwable $e) {
            Log::error('fromUrl error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to download image'], 500);
        }
    }

    private function deleteFileIfExists($publicPath)
    {
        if (!$publicPath) return;
        // $publicPath example: /storage/sliders/xyz.jpg -> convert to storage path
        $relative = ltrim($publicPath, '/'); // storage/sliders/xyz.jpg
        if (str_starts_with($relative, 'storage/')) {
            $diskPath = substr($relative, strlen('storage/'));
            Storage::disk('public')->delete($diskPath);
        }
    }
}
