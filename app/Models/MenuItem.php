<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'path',
        'slug',
        'icon',
        'order',
        'is_active',
        'type',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the category for the menu item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

