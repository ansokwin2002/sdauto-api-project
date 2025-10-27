<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    // GET /api/home-settings
    public function index()
    {
        $items = Setting::all();
        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    // POST /api/home-settings
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $item = Setting::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Home settings created successfully',
            'data' => $item,
        ], 201);
    }

    // GET /api/home-settings/{id}
    public function show($id)
    {
        $item = Setting::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Home settings not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    // PUT/PATCH /api/home-settings/{id}
    public function update(Request $request, $id)
    {
        $item = Setting::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Home settings not found'], 404);
        }
        $data = $this->validateData($request);
        $item->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Home settings updated successfully',
            'data' => $item,
        ]);
    }

    private function validateData(Request $request): array
    {
        $rules = [
            'address' => ['nullable','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:255'],
            'logo' => ['nullable','string','max:1024'],
            'title' => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'welcome_logo' => ['nullable','string','max:1024'],
            'title_welcome' => ['nullable','string','max:255'],
            'description_welcome' => ['nullable','string'],
            'why_choose_logo' => ['nullable','string','max:1024'],
            'why_choose_title' => ['nullable','string','max:255'],
            'why_choose_title1' => ['nullable','string','max:255'],
            'why_choose_description1' => ['nullable','string'],
            'why_choose_title2' => ['nullable','string','max:255'],
            'why_choose_description2' => ['nullable','string'],
            'why_choose_title3' => ['nullable','string','max:255'],
            'why_choose_description3' => ['nullable','string'],
            'why_choose_title4' => ['nullable','string','max:255'],
            'why_choose_description4' => ['nullable','string'],
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
