<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionalBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'image_left_url',
        'image_right_url',
        'background_color',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

