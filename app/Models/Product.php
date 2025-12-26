<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'brand',
        'brand_id',
        'category',
        'part_number',
        'condition',
        'quantity',
        'original_price',
        'price',
        'description',
        'images',
        'videos',
        'is_active'
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = ['deleted_at'];

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('quantity', '>', 0)->where('quantity', '<=', $threshold);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%")
              ->orWhere('part_number', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeOnDiscount($query)
    {
        return $query->whereNotNull('original_price')
                     ->whereColumn('price', '<', 'original_price');
    }

    // ==================== ATTRIBUTES ====================

    public function setImagesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['images'] = json_encode($value, JSON_UNESCAPED_SLASHES);
        } else {
            $decoded = json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['images'] = json_encode($decoded, JSON_UNESCAPED_SLASHES);
            } else {
                $this->attributes['images'] = $value;
            }
        }
    }

    public function getImagesAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        $images = json_decode($value, true);

        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        return is_array($images) ? $images : [];
    }

    public function setVideosAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['videos'] = json_encode($value, JSON_UNESCAPED_SLASHES);
        } else {
            $decoded = json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['videos'] = json_encode($decoded, JSON_UNESCAPED_SLASHES);
            } else {
                $this->attributes['videos'] = $value;
            }
        }
    }

    public function getVideosAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        $videos = json_decode($value, true);

        if (is_string($videos)) {
            $videos = json_decode($videos, true);
        }

        return is_array($videos) ? $videos : [];
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedOriginalPriceAttribute()
    {
        return $this->original_price ? '$' . number_format($this->original_price, 2) : null;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->original_price || $this->original_price <= $this->price) {
            return 0;
        }

        return round((($this->original_price - $this->price) / $this->original_price) * 100, 1);
    }

    public function getDiscountAmountAttribute()
    {
        if (!$this->original_price || $this->original_price <= $this->price) {
            return 0;
        }

        return $this->original_price - $this->price;
    }

    public function getStockStatusAttribute()
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->quantity <= 10) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    public function getPrimaryImageAttribute()
    {
        return !empty($this->images) ? $this->images[0] : null;
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->price;
    }

    public function getTotalOriginalValueAttribute()
    {
        return $this->quantity * ($this->original_price ?? $this->price);
    }

    // ==================== METHODS ====================

    public function isInStock()
    {
        return $this->quantity > 0;
    }

    public function isLowStock($threshold = 10)
    {
        return $this->quantity > 0 && $this->quantity <= $threshold;
    }

    public function hasDiscount()
    {
        return $this->original_price && $this->original_price > $this->price;
    }

    public function isOnSale()
    {
        return $this->hasDiscount();
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the brand that owns the product.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
