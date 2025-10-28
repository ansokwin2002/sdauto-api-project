<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Contact::all();
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
        $item = Contact::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Contact created successfully',
            'data' => $item,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Contact::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Contact not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Contact::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Contact not found'], 404);
        }
        $data = $this->validateData($request);
        $item->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Contact updated successfully',
            'data' => $item,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Contact::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Contact not found'], 404);
        }
        $item->delete();
        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully',
        ]);
    }

    private function validateData(Request $request): array
    {
        $rules = [
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'whatsApp' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'business_hour' => ['nullable', 'string', 'max:255'],
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
