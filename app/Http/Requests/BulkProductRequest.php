<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'action' => 'required|in:activate,deactivate,delete,update_stock,update_price,apply_discount',
            'product_ids' => 'required|array|min:1|max:100',
            'product_ids.*' => 'integer|exists:products,id',
            'quantity' => 'required_if:action,update_stock|integer|min:0',
            'operation' => 'required_if:action,update_stock|in:set,add,subtract',
            'price' => 'required_if:action,update_price|numeric|min:0',
            'original_price' => 'sometimes|nullable|numeric|min:0',
            'discount_percentage' => 'required_if:action,apply_discount|numeric|min:0|max:100',
        ];
    }
}
