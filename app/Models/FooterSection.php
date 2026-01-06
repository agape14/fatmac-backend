<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSection extends Model
{
    protected $fillable = [
        'position',
        'title',
        'content',
        'logo_url',
        'description',
        'phone',
        'email',
        'address',
        'links',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'links' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
