<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\YoutubeUrl;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $productId = $this->route('product');
        if (is_object($productId)) {
            $productId = $productId->id;
        }

        return [
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
            'part_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products')->ignore($productId)->whereNull('deleted_at')
            ],
            'condition' => 'required|in:New,Used,Refurbished',
            'quantity' => 'required|integer|min:0|max:999999',
            'original_price' => 'nullable|numeric|min:0|max:99999999.99',
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
                function ($attribute, $value, $fail) {
                    $originalPrice = $this->input('original_price');
                    if ($originalPrice > 0 && $value > $originalPrice) {
                        $fail('The current price cannot be higher than the original price.');
                    }
                },
            ],
            'description' => 'nullable|string|max:2000',
            'images' => 'nullable|array|max:20',
            'images.*' => ['bail', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'image_urls' => 'nullable|array|max:20',
            'image_urls.*' => 'string|url|max:500',
            'videos' => 'nullable|array|max:5',
            'videos.*' => ['string', 'max:500', new YoutubeUrl],
            'is_active' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Product name is required.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'brand.required' => 'Brand is required.',
            'brand.max' => 'Brand cannot exceed 100 characters.',
            'part_number.required' => 'Part number is required.',
            'part_number.unique' => 'Part number already exists.',
            'condition.required' => 'Condition is required.',
            'condition.in' => 'Condition must be New, Used, or Refurbished.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity cannot be negative.',
            'original_price.numeric' => 'Original price must be a valid number.',
            'original_price.min' => 'Original price cannot be negative.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
        ];
    }
}