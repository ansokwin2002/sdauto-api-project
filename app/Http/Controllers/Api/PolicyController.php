<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Policy::all();
        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $item = Policy::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Policy created successfully',
            'data' => $item,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Policy::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Policy not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Policy::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Policy not found'], 404);
        }
        $data = $this->validateData($request);
        $item->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Policy updated successfully',
            'data' => $item,
        ]);
    }



    private function validateData(Request $request): array
    {
        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'privacy' => ['nullable', 'string'],
            'warranty' => ['nullable', 'string'],
            'shipping' => ['nullable', 'string'],
            'order_cancellation' => ['nullable', 'string'],
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
