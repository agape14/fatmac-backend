<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'price',
        'discount_percentage',
        'stock',
        'condition',
        'category',
        'image_url', // Mantener para compatibilidad
        'is_new',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'is_new' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get the user (vendor) that owns the product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the product.
     */
    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the images for the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    /**
     * Calculate the discounted price.
     */
    public function getDiscountedPriceAttribute(): float
    {
        if ($this->discount_percentage && $this->discount_percentage > 0) {
            return $this->price * (1 - $this->discount_percentage / 100);
        }
        return $this->price;
    }
}
