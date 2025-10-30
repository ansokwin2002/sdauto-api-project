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
            'brand' => $this->getBrandName(), // Keep for backward compatibility
            'brand_id' => $this->brand_id,
            'brand_info' => $this->getBrandInfo(),
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

    /**
     * Get brand information safely
     */
    private function getBrandInfo()
    {
        // If brand relationship is loaded and is an object
        if ($this->relationLoaded('brand') && $this->brand && is_object($this->brand)) {
            return [
                'id' => $this->brand->id,
                'brand_name' => $this->brand->brand_name,
            ];
        }

        // If brand_id exists but relationship not loaded, fetch it
        if ($this->brand_id) {
            $brand = \App\Models\Brand::find($this->brand_id);
            return $brand ? [
                'id' => $brand->id,
                'brand_name' => $brand->brand_name,
            ] : null;
        }

        return null;
    }

    /**
     * Get brand name safely for backward compatibility
     */
    private function getBrandName()
    {
        // If brand relationship is loaded and is an object
        if ($this->relationLoaded('brand') && $this->brand && is_object($this->brand)) {
            return $this->brand->brand_name;
        }

        // If brand_id exists but relationship not loaded, fetch it
        if ($this->brand_id) {
            $brand = \App\Models\Brand::find($this->brand_id);
            return $brand ? $brand->brand_name : null;
        }

        // Fallback to the old string brand field if it exists
        if (isset($this->attributes['brand']) && is_string($this->attributes['brand'])) {
            return $this->attributes['brand'];
        }

        return null;
    }
}