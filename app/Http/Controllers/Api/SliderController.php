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
    public function index()
    {
        $items = Slider::orderBy('ordering')->orderBy('id')->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['required','image','mimes:jpg,jpeg,png,webp,gif','max:5120'],
            'ordering' => ['nullable','integer','min:0']
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Store file in /public/uploads/sliders
        $path = $request->file('image')->store('uploads/sliders', 'public');

        $nextOrdering = (int) (Slider::max('ordering') ?? 0) + 1;
        $payload = [
            'image' => '/' . $path, // <-- changed
            'ordering' => $request->filled('ordering') ? (int) $request->ordering : $nextOrdering
        ];

        $item = Slider::create($payload);
        return response()->json(['success' => true, 'message' => 'Slider created successfully', 'data' => $item], 201);
    }

    public function show($id)
    {
        $item = Slider::find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Slider not found'], 404);
        return response()->json(['success' => true, 'data' => $item]);
    }

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
            $this->deleteFileIfExists($item->image);
            $path = $request->file('image')->store('uploads/sliders', 'public');
            $item->image = '/' . $path;
        }

        if ($request->filled('ordering')) {
            $item->ordering = (int) $request->ordering;
        }

        $item->save();
        return response()->json(['success' => true, 'message' => 'Slider updated successfully', 'data' => $item]);
    }

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

        $item->ordering = (int) $request->ordering;
        $item->save();
        return response()->json(['success' => true, 'message' => 'Ordering updated successfully', 'data' => $item]);
    }

    public function destroy($id)
    {
        $item = Slider::find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Slider not found'], 404);

        $this->deleteFileIfExists($item->image);
        $item->delete();

        return response()->json(['success' => true, 'message' => 'Slider deleted successfully']);
    }

    public function fromUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => ['required','url'],
            'ordering' => ['nullable','integer','min:0']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $url = $request->url;

        try {
            $client = new Client(['timeout' => 15, 'verify' => false]);
            $response = $client->get($url, ['stream' => true]);

            $contentType = $response->getHeaderLine('Content-Type');
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
            if (!isset($allowed[$contentType])) {
                return response()->json(['success' => false, 'message' => 'Unsupported image type'], 422);
            }

            $ext = $allowed[$contentType];
            $maxBytes = 5 * 1024 * 1024;

            $body = '';
            $stream = $response->getBody();
            while (!$stream->eof()) {
                $chunk = $stream->read(65536);
                $body .= $chunk;
                if (strlen($body) > $maxBytes) {
                    return response()->json(['success'=>false,'message'=>'Image exceeds 5MB limit'],422);
                }
            }

            // New path
            $filename = 'uploads/sliders/' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $ext;
            Storage::disk('public')->put($filename, $body);

            $nextOrdering = (int) (Slider::max('ordering') ?? 0) + 1;

            $item = Slider::create([
                'image' => '/' . $filename,
                'ordering' => $request->filled('ordering') ? (int) $request->ordering : $nextOrdering
            ]);

            return response()->json(['success'=>true,'message'=>'Slider created successfully','data'=>$item], 201);

        } catch (\Throwable $e) {
            Log::error('fromUrl error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to download image'], 500);
        }
    }

    private function deleteFileIfExists($publicPath)
    {
        if (!$publicPath) return;

        $relative = ltrim($publicPath, '/'); // uploads/sliders/xxx.jpg
        Storage::disk('public')->delete($relative);
    }
}
