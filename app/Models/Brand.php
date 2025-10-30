<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands';

    protected $fillable = [
        'brand_name',
        'slug',
    ];

    // ==================== BOOT METHODS ====================

    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug if not provided
        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->brand_name);

                // Ensure slug is unique
                $originalSlug = $brand->slug;
                $counter = 1;
                while (static::where('slug', $brand->slug)->exists()) {
                    $brand->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        // Update slug if brand_name changes
        static::updating(function ($brand) {
            if ($brand->isDirty('brand_name') && empty($brand->getOriginal('slug'))) {
                $brand->slug = Str::slug($brand->brand_name);

                // Ensure slug is unique
                $originalSlug = $brand->slug;
                $counter = 1;
                while (static::where('slug', $brand->slug)->where('id', '!=', $brand->id)->exists()) {
                    $brand->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    // ==================== SCOPES ====================

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    // ==================== METHODS ====================

    /**
     * Generate a unique slug for the brand
     */
    public function generateSlug()
    {
        $slug = Str::slug($this->brand_name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the products for the brand.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
