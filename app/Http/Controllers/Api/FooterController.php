<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Footer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FooterController extends Controller
{
    // GET /api/public/footer
    public function index()
    {
        $items = Footer::all();
        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    // POST /api/admin/footer
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $item = Footer::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Footer created successfully',
            'data' => $item,
        ], 201);
    }

    // GET /api/admin/footer/{id}
    public function show($id)
    {
        $item = Footer::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Footer not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    // PUT/PATCH /api/admin/footer/{id}
    public function update(Request $request, $id)
    {
        $item = Footer::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Footer not found'], 404);
        }
        $data = $this->validateData($request);
        $item->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Footer updated successfully',
            'data' => $item,
        ]);
    }

    // DELETE /api/admin/footer/{id}
    public function destroy($id)
    {
        $item = Footer::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Footer not found'], 404);
        }
        $item->delete();
        return response()->json([
            'success' => true,
            'message' => 'Footer deleted successfully',
        ]);
    }

    private function validateData(Request $request): array
    {
        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'contact' => ['nullable', 'string'],
            'business_hour' => ['nullable', 'string'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            abort(response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422));
        }
        return $validator->validated();
    }
}
