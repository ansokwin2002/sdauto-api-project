<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryBrand extends Model
{
    use HasFactory;

    protected $table = 'category_brands';

    protected $fillable = [
        'brand',
        'slug',
        'logo',
        'ordering',
    ];

    protected $casts = [
        'ordering' => 'integer',
    ];

    // ==================== BOOT METHODS ====================

    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug if not provided
        static::creating(function ($categoryBrand) {
            if (empty($categoryBrand->slug)) {
                $categoryBrand->slug = Str::slug($categoryBrand->brand);
                
                // Ensure slug is unique
                $originalSlug = $categoryBrand->slug;
                $counter = 1;
                while (static::where('slug', $categoryBrand->slug)->exists()) {
                    $categoryBrand->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        // Update slug if brand changes
        static::updating(function ($categoryBrand) {
            if ($categoryBrand->isDirty('brand') && empty($categoryBrand->getOriginal('slug'))) {
                $categoryBrand->slug = Str::slug($categoryBrand->brand);
                
                // Ensure slug is unique
                $originalSlug = $categoryBrand->slug;
                $counter = 1;
                while (static::where('slug', $categoryBrand->slug)->where('id', '!=', $categoryBrand->id)->exists()) {
                    $categoryBrand->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    // ==================== SCOPES ====================

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordering')->orderBy('brand');
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    // ==================== METHODS ====================

    /**
     * Generate a unique slug for the category brand
     */
    public function generateSlug()
    {
        $slug = Str::slug($this->brand);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
