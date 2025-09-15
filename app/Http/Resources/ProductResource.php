<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'category' => $this->category,
            'part_number' => $this->part_number,
            'condition' => $this->condition,
            'quantity' => $this->quantity,
            'original_price' => $this->original_price,
            'price' => $this->price,
            'formatted_original_price' => $this->formatted_original_price,
            'formatted_price' => $this->formatted_price,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'has_discount' => $this->hasDiscount(),
            'is_on_sale' => $this->isOnSale(),
            'description' => $this->description,
            'images' => is_array($this->images) ? array_map(function($image) {
                return asset('storage/' . $image);
            }, $this->images) : [],
            'videos' => $this->videos ?? [],
            'primary_image' => $this->primary_image,
            'is_active' => $this->is_active,
            'in_stock' => $this->isInStock(),
            'stock_status' => $this->stock_status,
            'is_low_stock' => $this->isLowStock(),
            'total_value' => $this->total_value,
            'total_original_value' => $this->total_original_value,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}