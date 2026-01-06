<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopBannerSetting extends Model
{
    protected $fillable = [
        'text',
        'background_color',
        'text_color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
